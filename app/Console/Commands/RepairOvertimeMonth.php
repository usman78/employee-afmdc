<?php

namespace App\Console\Commands;

use App\Models\OvertimeApplication;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RepairOvertimeMonth extends Command
{
    protected $signature = 'overtime:repair-month
                            {month : Month to repair in YYYY-MM format}
                            {--dry-run : Show the changes without saving them}';

    protected $description = 'Recalculate overtime amounts using the selected month day count';

    public function handle(): int
    {
        $month = $this->argument('month');

        try {
            $monthStart = Carbon::createFromFormat('!Y-m', $month);
        } catch (\Throwable) {
            $this->error('The month must be in YYYY-MM format.');

            return self::FAILURE;
        }

        if ($monthStart->format('Y-m') !== $month) {
            $this->error('The month must be in YYYY-MM format.');

            return self::FAILURE;
        }

        $applications = OvertimeApplication::where('salary_month', $month)->get();

        if ($applications->isEmpty()) {
            $this->info("No overtime applications found for {$month}.");

            return self::SUCCESS;
        }

        $changes = [];
        foreach ($applications as $application) {
            if (! $application->shift_start || ! $application->shift_end) {
                $this->warn("Skipped application {$application->id}: shift times are missing.");
                continue;
            }

            $shiftMinutes = max(1, Carbon::parse($application->shift_start)
                ->diffInMinutes(Carbon::parse($application->shift_end)));
            $hourlyRate = (float) $application->gross_salary
                / $monthStart->daysInMonth
                / max(1, $shiftMinutes / 60);
            $calculatedAmount = $this->calculateAmount($hourlyRate, (int) $application->overtime_minutes);
            $sanctionedAmount = $application->sanctioned_minutes === null
                ? null
                : $this->calculateAmount($hourlyRate, (int) $application->sanctioned_minutes);

            $changes[] = [
                'id' => $application->id,
                'old_rate' => (float) $application->hourly_rate,
                'new_rate' => $hourlyRate,
                'old_amount' => (float) $application->calculated_amount,
                'new_amount' => $calculatedAmount,
                'old_sanctioned' => $application->sanctioned_amount === null ? null : (float) $application->sanctioned_amount,
                'new_sanctioned' => $sanctionedAmount,
            ];

            if (! $this->option('dry-run')) {
                $application->forceFill([
                    'hourly_rate' => $hourlyRate,
                    'calculated_amount' => $calculatedAmount,
                    'sanctioned_amount' => $sanctionedAmount,
                ])->save();
            }
        }

        $this->table(
            ['ID', 'Old rate', 'New rate', 'Old amount', 'New amount', 'Old sanctioned', 'New sanctioned'],
            array_map(fn (array $change) => [
                $change['id'],
                number_format($change['old_rate'], 2),
                number_format($change['new_rate'], 2),
                number_format($change['old_amount'], 2),
                number_format($change['new_amount'], 2),
                $change['old_sanctioned'] === null ? '-' : number_format($change['old_sanctioned'], 2),
                $change['new_sanctioned'] === null ? '-' : number_format($change['new_sanctioned'], 2),
            ], $changes)
        );

        $action = $this->option('dry-run') ? 'would be repaired' : 'repaired';
        $this->info(count($changes) . " application(s) {$action} for {$month} using {$monthStart->daysInMonth} days.");

        return self::SUCCESS;
    }

    private function calculateAmount(float $hourlyRate, int $minutes): float
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes <= 25) {
            $remainingMinutes = 0;
        } elseif ($remainingMinutes <= 45) {
            $remainingMinutes = 30;
        } else {
            $hours++;
            $remainingMinutes = 0;
        }

        return round(($hourlyRate * (($hours * 60) + $remainingMinutes)) / 60);
    }
}
