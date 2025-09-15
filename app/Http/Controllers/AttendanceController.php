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
    public function attendance($emp_code)
    {
        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        $emp_category = Employee::select('catg_code', 'st_time', 'end_time')->where('emp_code', $emp_code)->first();
        $totalMins = 480;

        if($emp_category->catg_code == 2){
            $totalMins = 360;
        }       

        // Define national holidays
        $holidays = [
            '2025-03-31', // Eid
            '2025-04-01', // Eid
            '2025-04-02', // Eid
            '2025-05-01', // Labor Day
            '2025-05-07', // Holiday due to hostility
            '2025-06-09', // Eid
            '2025-06-10', // Eid
            '2025-06-11', // Eid
            '2025-07-05', // Ashura
            '2025-08-14', // Independance Day
            '2025-11-09', // Iqbal Day
            '2025-12-25', // Christmas
        ];

        // Get attendance records (excluding Sundays)
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::today();     
        $allDates = collect();
        $tempDate = $start_date->copy();

        while ($tempDate->lte($end_date)) {
            $dateString = $tempDate->toDateString(); // Format: YYYY-MM-DD
            $leaveType = null;  
            $isLeave = false;
            $isHoliday = in_array($dateString, $holidays);
            $minsWorked = 0;
            $timeLogs = [];
            
            if ($tempDate->isSunday() || $isHoliday) {
                // Manually create a placeholder record for Sundays
                $allDates->push([
                    'at_date' => $dateString,
                    'timein' => null,
                    'timeout' => null,
                    'time_logs' => [],
                    'is_sunday' => true,
                    'is_holiday' => $isHoliday,
                    'is_leave' => false
                ]);
            } else {
                // Fetch all attendance records for this date
                $attendanceRecords = Attendance::whereRaw("TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                                            ->where('emp_code', $emp_code)
                                            ->whereNull('att_stat')
                                            ->get();
                //  Fetch the record for the date available in the database
                if ($attendanceRecords->isNotEmpty()) {
                    $leave = null;
                    // Determine if multiple records exist
                    foreach ($attendanceRecords as $record) {
                        if (ifLeaveExists($emp_code, $dateString)) {
                            $isLeave = true;
                            $leave = Leave::whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                                ->whereRaw("TRUNC(to_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                                ->where('emp_code', $emp_code)
                                ->first();
                            $leaveType = $leave->leave_code;   
                        }
                        $timein = $record->timein;
                        $timeout = $record->timeout;
                        $minsWorked += minutesWorked($timein, $timeout);
                        $timeLogs[] = [
                            'timein' => $timein,
                            'timeout' => $timeout
                        ];
                    }
                    $leaveType = leaveDescription($leaveType, $leave ? $leave->from_date : null, $leave ? $leave->to_date : null);
                    
                    $leaveRemark = null;
                    
                    if (!$isLeave) {
                        if ($minsWorked >= ($totalMins / 2) && $minsWorked < $totalMins - 120) {
                            $leaveRemark = 'Half Day Eligible';
                        } elseif ($minsWorked < ($totalMins / 2)) {
                            $leaveRemark = 'Full Day Eligible';
                        }
                    }
                     
                    $allDates->push([
                        'at_date' => $dateString,
                        'timein' => $timein,
                        'timeout' => $timeout,
                        'time_logs' => $timeLogs ?? [],
                        'is_sunday' => false,
                        'is_holiday' => $isHoliday,
                        'is_leave' => $isLeave,
                        'leave_type' => $isLeave ? $leaveType : null,
                        'worked_minutes' => $minsWorked,
                        'short_duty_status' => $leaveRemark
                    ]);
                    
                } else {
                    if (ifLeaveExists($emp_code, $dateString)) {
                        $isLeave = true;
                        $leave = Leave::whereRaw("TRUNC(from_date) <= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                            ->whereRaw("TRUNC(to_date) >= TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                            ->where('emp_code', $emp_code)
                            ->first();
                        $leaveType = $leave->leave_code;
                        $leaveType = leaveDescription($leaveType, $leave ? $leave->from_date : null, $leave ? $leave->to_date : null);   
                    }
                    // Manually create a placeholder record for non-leave days
                    $allDates->push([
                        'at_date' => $dateString,
                        'timein' => null,
                        'timeout' => null,
                        'time_logs' => [],
                        'is_sunday' => false,
                        'is_holiday' => $isHoliday,
                        'is_leave' => $isLeave ? true : false,
                        'leave_type' => $isLeave ? $leaveType : null,
                    ]);
                }
            }
            $tempDate->addDay();
        }

        $allDates = $allDates->sortByDesc('at_date')->values();
        $employee = Employee::where('emp_code', $emp_code)->first();

        $leaves = Leave::where('emp_code', $emp_code)
            ->whereNot('status', 9)
            ->where('from_date',  '>=' , $start_date)
            ->where('to_date', '<=', $end_date)
            ->get(); 

        return view('attendance', [
            'attendance' => $allDates,
            // 'emp_code' => $emp_code,
            'leaves' => $leaves,
            'emp_name' => $employee ? ucfirst($employee->name) : 'Unknown Employee'
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