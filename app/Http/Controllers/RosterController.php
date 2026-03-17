<?php

namespace App\Http\Controllers;

use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class RosterController extends Controller
{
    public function index(Request $request, $empCode)
    {
        $authUser = Auth::user();
        if ($authUser->emp_code != $empCode) {
            return redirect()->route('home');
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return view('attendance', $this->buildAttendanceData($empCode, $startDate, $endDate));
    }

    private function buildAttendanceData($empCode, $startDate = null, $endDate = null): array
    {
        $start = $this->parseRosterDateTime($startDate) ?? Carbon::now()->startOfMonth();
        $end = $this->parseRosterDateTime($endDate) ?? Carbon::today();
        if ($end->gt(Carbon::today())) {
            $end = Carbon::today();
        }

        $rows = Roster::where('emp_code', $empCode)
            ->whereBetween('dated', [$start->toDateString(), $end->toDateString()])
            ->orderBy('dated')
            ->get();

        $normalized = $rows->map(function ($row) {
            return $this->normalizeRosterRow($row);
        });

        $attendance = $normalized
            ->groupBy(function ($row) {
                return $this->normalizeDateKey($row['dated'] ?? null);
            })
            ->map(function (Collection $group) {
                $dateKey = $this->normalizeDateKey($group->first()['dated'] ?? null);
                $timeLogs = $this->buildTimeLogs($group);

                $isSunday = $this->isSundayGroup($group);
                $isHoliday = $this->isHolidayGroup($group);

                $leaveType = $this->buildLeaveLabelFromGroup($group);
                if ($isSunday || $isHoliday) {
                    $leaveType = null;
                }

                return [
                    'at_date' => $dateKey,
                    'time_logs' => $timeLogs,
                    'late_minutes' => $this->maxIntFromGroup($group, 'late_coming'),
                    'early_minutes' => $this->maxIntFromGroup($group, 'early_exit_min'),
                    'is_sunday' => $isSunday,
                    'is_holiday' => $isHoliday,
                    'leave_type' => $leaveType,
                ];
            })
            ->sortByDesc('at_date')
            ->values();

        $authUser = Auth::user();
        $empName = $normalized->pluck('name')->filter()->first() ?? ($authUser ? $authUser->name : null) ?? 'Unknown Employee';

        return [
            'attendance' => $attendance,
            'leaves' => collect(),
            'leave_counts' => $this->countLeaveCodes($normalized),
            'emp_name' => ucfirst($empName),
            'emp_code' => $empCode,
            'report_start_date' => $start->toDateString(),
            'report_end_date' => $end->toDateString(),
        ];
    }

    private function normalizeRosterRow($row): array
    {
        $attributes = $row instanceof \Illuminate\Database\Eloquent\Model ? $row->getAttributes() : (array) $row;
        $attributes = array_change_key_case($attributes, CASE_LOWER);

        return [
            'emp_code' => $attributes['emp_code'] ?? null,
            'name' => $attributes['name'] ?? null,
            'dated' => $attributes['dated'] ?? null,
            'day' => $attributes['day'] ?? null,
            'in_date' => $attributes['in_date'] ?? null,
            'out_date' => $attributes['out_date'] ?? null,
            'leave_from' => $attributes['leave_from'] ?? null,
            'leave_to' => $attributes['leave_to'] ?? null,
            'leav_code' => $attributes['leav_code'] ?? null,
            'leav_name' => $attributes['leav_name'] ?? null,
            'leave_span' => $attributes['leave_span'] ?? null,
            'late_coming' => $attributes['late_coming'] ?? null,
            'early_exit_min' => $attributes['early_exit_min'] ?? null,
            'total_stay_minutes' => $attributes['total_stay_minutes'] ?? null,
            'att_attempts' => $attributes['att_attempts'] ?? null,
        ];
    }

    private function normalizeDateKey($value): string
    {
        $date = $this->parseRosterDateTime($value);
        return $date ? $date->toDateString() : '';
    }

    private function parseRosterDateTime($value, bool $requireTime = false): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if ($requireTime && !preg_match('/\d{1,2}:\d{2}/', $value)) {
            return null;
        }

        $formats = [
            'd/m/Y h:i:s a',
            'd/m/Y h:i a',
            'd/m/Y H:i:s',
            'd/m/Y',
            'Y-m-d H:i:s',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            if ($requireTime && str_contains($format, 'd/m/Y') && !str_contains($format, 'H') && !str_contains($format, 'h')) {
                continue;
            }
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function buildTimeLogs(Collection $group): array
    {
        $logs = [];

        foreach ($group as $row) {
            $in = $this->parseRosterDateTime($row['in_date'] ?? null, true);
            $out = $this->parseRosterDateTime($row['out_date'] ?? null, true);
            $total = $this->toInt($row['total_stay_minutes'] ?? null);

            if (!$in && !$out) {
                continue;
            }

            if ($total <= 0 && $in && $out && $in->equalTo($out)) {
                continue;
            }

            $logs[] = [
                'timein' => $in ? $in->toDateTimeString() : null,
                'timeout' => $out ? $out->toDateTimeString() : null,
            ];
        }

        $unique = [];
        $seen = [];
        foreach ($logs as $log) {
            $key = ($log['timein'] ?? '') . '|' . ($log['timeout'] ?? '');
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $log;
        }

        usort($unique, function ($a, $b) {
            return strcmp($a['timein'] ?? '', $b['timein'] ?? '');
        });

        return $unique;
    }

    private function isSundayGroup(Collection $group): bool
    {
        return $group->contains(function ($row) {
            $day = strtoupper(trim((string) ($row['day'] ?? '')));
            $leaveName = strtoupper(trim((string) ($row['leav_name'] ?? '')));
            return $day === 'SUN' || $leaveName === 'WR';
        });
    }

    private function isHolidayGroup(Collection $group): bool
    {
        return $group->contains(function ($row) {
            $leaveName = strtoupper(trim((string) ($row['leav_name'] ?? '')));
            $leaveCode = trim((string) ($row['leav_code'] ?? ''));
            return $leaveName !== '' && $leaveName !== 'WR' && $leaveCode === '';
        });
    }

    private function buildLeaveLabelFromGroup(Collection $group): ?string
    {
        $row = $group->first(function ($item) {
            return trim((string) ($item['leav_code'] ?? '')) !== ''
                || in_array(strtoupper(trim((string) ($item['leav_name'] ?? ''))), ['CL', 'SL', 'EL'], true);
        });

        if (!$row) {
            return null;
        }

        return $this->buildLeaveLabel($row);
    }

    private function buildLeaveLabel(array $row): ?string
    {
        $code = trim((string) ($row['leav_code'] ?? ''));
        $name = strtoupper(trim((string) ($row['leav_name'] ?? '')));

        if ($code === '') {
            $code = match ($name) {
                'CL' => '1',
                'SL' => '8',
                'EL' => '3',
                default => '',
            };
        }

        if ($code === '') {
            return null;
        }

        $label = match ($code) {
            '1' => 'Casual',
            '2' => 'Medical',
            '3' => 'Annual',
            '5' => 'Without Pay',
            '8' => 'Short',
            '12' => 'Outdoor Duty',
            default => $name !== '' ? $name : 'Leave',
        };

        $span = strtoupper(trim((string) ($row['leave_span'] ?? '')));
        $fromAny = $this->parseRosterDateTime($row['leave_from'] ?? null);
        $toAny = $this->parseRosterDateTime($row['leave_to'] ?? null);
        $fromTime = $this->parseRosterDateTime($row['leave_from'] ?? null, true);
        $toTime = $this->parseRosterDateTime($row['leave_to'] ?? null, true);

        $isFullDay = false;
        if ($span !== '') {
            $isFullDay = str_contains($span, 'FULL');
        } elseif ($fromAny && $toAny) {
            $isFullDay = $fromAny->isStartOfDay() && $toAny->isStartOfDay();
        }

        if ($isFullDay) {
            return "Full Day {$label} Leave";
        }

        if ($fromTime && $toTime) {
            return $label . ' ' . $fromTime->format('H:i') . ' to ' . $toTime->format('H:i');
        }

        return $label;
    }

    private function maxIntFromGroup(Collection $group, string $key): int
    {
        $max = 0;
        foreach ($group as $row) {
            $value = $this->toInt($row[$key] ?? null);
            if ($value > $max) {
                $max = $value;
            }
        }
        return $max;
    }

    private function toInt($value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) round($value);
        }

        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    private function countLeaveCodes(Collection $rows): array
    {
        $byDate = $rows->groupBy(function ($row) {
            return $this->normalizeDateKey($row['dated'] ?? null);
        });

        $counts = [
            'casual' => 0,
            'medical' => 0,
            'annual' => 0,
            'outdoor_duty' => 0,
        ];

        foreach ($byDate as $group) {
            $row = $group->first(function ($item) {
                return trim((string) ($item['leav_code'] ?? '')) !== '';
            });

            if (!$row) {
                continue;
            }

            $code = trim((string) $row['leav_code']);
            if ($code === '1') {
                $counts['casual']++;
            } elseif ($code === '2') {
                $counts['medical']++;
            } elseif ($code === '3') {
                $counts['annual']++;
            } elseif ($code === '12') {
                $counts['outdoor_duty']++;
            }
        }

        return $counts;
    }
}
