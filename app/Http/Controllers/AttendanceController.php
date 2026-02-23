<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 
use App\Models\Leave;

class AttendanceController extends Controller
{
    private function getRamadanDeductionMinutes($empCategory, Carbon $workDate): int
    {
        $ramadanStart = Carbon::create(2026, 2, 19)->startOfDay();
        $ramadanEnd   = Carbon::create(2026, 3, 20)->endOfDay();

        if (!$workDate->betweenIncluded($ramadanStart, $ramadanEnd)) {
            return 0;
        }

        $locaCode = (int) $empCategory->loca_code;
        $catgCode = (int) $empCategory->catg_code;

        if ($locaCode === 1 && ($catgCode === 1 || $catgCode === 4)) {
            return 90;
        }

        if ($locaCode === 1 && $catgCode === 2) {
            return 60;
        }

        if ($locaCode === 2 && $catgCode === 1) {
            return $workDate->isFriday() ? 210 : 60;
        }

        if ($locaCode === 2 && $catgCode === 2) {
            return $workDate->isFriday() ? 30 : 0;
        }

        return 0;
    }

    public function attendance($emp_code)
    {
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        $emp_category = Employee::select(
            'catg_code', 'loca_code', 'st_time', 'end_time', 'twh'
        )->where('emp_code', $emp_code)->first();

        /* -------------------------
        Required minutes
        -------------------------- */
        $totalMins = 480;

        if ($emp_category->catg_code == 2) {
            $totalMins = 360;
        } elseif ($emp_category->twh == 12) {
            $totalMins = 720;
        }

        /* -------------------------
        Holidays
        -------------------------- */
        $holidays = [
            '2025-03-31','2025-04-01','2025-04-02',
            '2025-05-01','2025-05-07',
            '2025-06-09','2025-06-10','2025-06-11',
            '2025-07-05','2025-08-14',
            '2025-11-09','2025-12-25'
        ];

        $start_date = Carbon::now()->startOfMonth();
        $end_date   = Carbon::today();

        $allDates = collect();
        $tempDate = $start_date->copy();

        while ($tempDate->lte($end_date)) {

            $dateString = $tempDate->toDateString();
            $isHoliday  = in_array($dateString, $holidays);

            /* ===============================
            SUNDAY / HOLIDAY
            ================================ */
            if ($tempDate->isSunday() || $isHoliday) {

                $allDates->push([
                    'at_date'   => $dateString,
                    'time_logs' => [],
                    'is_sunday' => $tempDate->isSunday(),
                    'is_holiday'=> $isHoliday,
                    'is_leave'  => false
                ]);

                $tempDate->addDay();
                continue;
            }

            /* ===============================
            FETCH ATTENDANCE
            ================================ */
            $attendanceRecords = Attendance::whereRaw(
                    "TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')", [$dateString]
                )
                ->where('emp_code', $emp_code)
                ->whereNull('att_stat')
                ->orderBy('timein')
                ->get();

            /* ===============================
            LEAVE DETECTION
            ================================ */
            $isLeave = false;
            $leaveType = null;
            $leaveStart = null;
            $leaveEnd   = null;
            $leaveMins  = 0;
            $isFullDayLeave = false;

            if (ifLeaveExists($emp_code, $dateString)) {

                $leave = Leave::whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                    ->whereRaw("TRUNC(to_date)   >= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                    ->where('emp_code', $emp_code)
                    ->whereNot('status', 9)
                    ->first();

                if ($leave) {
                    $isLeave = true;
                    $leaveType = leaveDescription(
                        $leave->leave_code,
                        $leave->from_date,
                        $leave->to_date
                    );

                    $from = Carbon::parse($leave->from_date);
                    $to   = Carbon::parse($leave->to_date);

                    if ($from->format('H:i:s') === '00:00:00' &&
                        $to->format('H:i:s')   === '00:00:00') {

                        $isFullDayLeave = true;

                    } else {
                        $leaveStart = $from;
                        $leaveEnd   = $to;
                        $leaveMins  = $from->diffInMinutes($to);
                    }
                }
            }

            /* ===============================
            NO ATTENDANCE RECORDS
            ================================ */
            if ($attendanceRecords->isEmpty()) {

                $allDates->push([
                    'at_date'   => $dateString,
                    'time_logs' => [],
                    'is_sunday' => false,
                    'is_holiday'=> $isHoliday,
                    'is_leave'  => $isLeave,
                    'leave_type'=> $isLeave ? $leaveType : null
                ]);

                $tempDate->addDay();
                continue;
            }

            /* ===============================
            SHIFT TIMES (DAY LEVEL)
            ================================ */
            $workDate = Carbon::parse($dateString);

            $startTimeCarbon = $workDate->copy()->setTimeFromTimeString($emp_category->st_time);
            $endTimeCarbon   = $workDate->copy()->setTimeFromTimeString($emp_category->end_time);

            if ($emp_category->twh != 12) {

                if ($emp_category->catg_code == 2 && $workDate->isFriday()) {
                    $totalMins = 300;
                    $startTimeCarbon->setTime(8,0);
                    $endTimeCarbon->setTime(13,0);

                } elseif ($emp_category->catg_code == 1 &&
                        $emp_category->loca_code == 2 &&
                        $workDate->isFriday()) {

                    $totalMins = 390;
                    $startTimeCarbon->setTime(8,0);
                    $endTimeCarbon->setTime(14,30);
                }
            }

            $ramadanDeductionMins = $this->getRamadanDeductionMinutes($emp_category, $workDate);
            $adjustedEndTimeCarbon = $endTimeCarbon->copy()->subMinutes($ramadanDeductionMins);

            /* ===============================
            BUILD TIME LOGS
            ================================ */
            $minsWorked = 0;
            $timeLogs   = [];

            foreach ($attendanceRecords as $record) {

                $worked = minutesWorked($record->timein, $record->timeout);
                $minsWorked += $worked;

                $timeLogs[] = [
                    'timein'          => $record->timein,
                    'timeout'         => $record->timeout,
                    'worked_minutes' => $worked
                ];
            }

            /* ===============================
            LATE / EARLY CALCULATION
            ================================ */
            $lateMins  = 0;
            $earlyMins = 0;

            if (!$isFullDayLeave) {

                $minIn  = Carbon::parse($attendanceRecords->min('timein'));
                if($attendanceRecords->whereNull('timeout')->isNotEmpty()) {
                    $maxOut = null;
                } else {
                    $maxOut = Carbon::parse($attendanceRecords->max('timeout'));
                }

                if ($emp_category->twh == 12) {

                    $required = $totalMins;

                    if ($leaveMins > 0) {
                        $required = max(0, $totalMins - $leaveMins);
                    }

                    $lateMins = max(0, $required - $minsWorked);

                } else {

                    if ($minIn->gt($startTimeCarbon)) {
                        $lateMins = $startTimeCarbon->diffInMinutes($minIn);
                    }

                    if($maxOut == null) {
                        $earlyMins = null;
                    }
                    else if ($maxOut->lt($adjustedEndTimeCarbon)) {
                        $earlyMins = $maxOut->diffInMinutes($adjustedEndTimeCarbon);
                    }

                    if ($leaveStart) {

                        if ($lateMins > 0) {
                            $overlapStart = max($startTimeCarbon, $leaveStart);
                            $overlapEnd   = min($minIn, $leaveEnd);

                            // if ($overlapStart < $overlapEnd) {
                            //     $lateMins -= $overlapStart->diffInMinutes($overlapEnd);
                            // }
                            $lateMins = 0; // compensation for partial leave
                        }

                        if ($earlyMins > 0) {
                            $overlapStart = max($maxOut, $leaveStart);
                            $overlapEnd   = min($adjustedEndTimeCarbon, $leaveEnd);

                            // if ($overlapStart < $overlapEnd) {
                            //     $earlyMins -= $overlapStart->diffInMinutes($overlapEnd);
                            // }
                            $earlyMins = 0; // compensation for partial leave
                        }
                    }
                }
            }
            // Log::info($dateString.' - Late: '.$lateMins.' Early: '.$earlyMins);
            $lateMins  = max(0, $lateMins);
            $earlyMins = max(0, $earlyMins);
            
            /* ===============================
            SHORT DUTY STATUS
            ================================ */
            $leaveRemark = null;

            if (!$isLeave) {
                if ($minsWorked >= ($totalMins / 2) && $minsWorked < $totalMins - 120) {
                    $leaveRemark = 'Half Day Eligible';
                } elseif ($minsWorked < ($totalMins / 2)) {
                    $leaveRemark = 'Full Day Eligible';
                }
            }

            /* ===============================
            FINAL PUSH
            ================================ */
            $allDates->push([
                'at_date'           => $dateString,
                'time_logs'         => $timeLogs,
                'worked_minutes'    => $minsWorked,
                'late_minutes'      => $lateMins,
                'early_minutes'     => $earlyMins,
                'is_sunday'         => false,
                'is_holiday'        => $isHoliday,
                'is_leave'          => $isLeave,
                'leave_type'        => $isLeave ? $leaveType : null,
                'short_duty_status' => $leaveRemark
            ]);

            $tempDate->addDay();
        }

        $attendance = $allDates->sortByDesc('at_date')->values();

        $employee = Employee::where('emp_code', $emp_code)->first();

        $leaves = Leave::where('emp_code', $emp_code)
            ->whereNot('status', 9)
            ->where('from_date', '>=', $start_date)
            ->where('to_date', '<=', $end_date)
            ->get();

        return view('attendance', [
            'attendance' => $attendance,
            'leaves'     => $leaves,
            'emp_name'   => $employee ? ucfirst($employee->name) : 'Unknown Employee'
        ]);
    }

}

function leaveDescription($leaveCode, $fromDate = null, $toDate = null)
{
    // identify full day leave using isStartOfDay and isEndOfDay methods
    if ($fromDate && $toDate) {
        $fromDate = Carbon::parse($fromDate);
        $toDate = Carbon::parse($toDate);
        if ($fromDate->isStartOfDay() && $toDate->isStartOfDay()) {
            switch ($leaveCode) {
                case '1':
                    return 'Full Day Casual Leave';
                case '2':
                    return 'Full Day Medical Leave';
                case '3':
                    return 'Full Day Annual Leave';
                case '5':
                    return 'Full Day Without Pay Leave';
                case '12':
                    return 'Full Day Outdoor Duty';
                default:
                    return 'Unknown Leave';
            }
        }
    }
    switch ($leaveCode) {
        case '1':
            return 'Casual ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '2':
            return 'Medical ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '3':
            return 'Annual '  . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '5':
            return 'Without Pay Leave';
        case '8':
            return 'Short ' . date('H:i', strtotime($fromDate)) . ' to ' . date('H:i',strtotime($toDate));
        case '12':
            return 'Outdoor Duty';
        default:
            return 'Unknown Leave';
    }
}

function minutesWorked($timein, $timeout) {
    if ($timein && $timeout) {
        $in = Carbon::parse($timein);
        $out = Carbon::parse($timeout);
        $workedMinutes = round($in->diffInMinutes($out));
        return $workedMinutes;
    }
    return 0;
}
