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
        $startTime = $emp_category->st_time;
        $endTime = $emp_category->end_time;

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

            if ($tempDate->isSunday() || $isHoliday) {
                // Manually create a placeholder record for Sundays
                $allDates->push([
                    'at_date' => $dateString,
                    'timein' => null,
                    'timeout' => null,
                    'is_sunday' => true,
                    'is_holiday' => $isHoliday,
                    'is_leave' => false
                ]);
            } else {
                // Fetch all attendance records for this date
                $attendanceRecords = Attendance::whereRaw("TRUNC(at_date) = TO_DATE(?, 'YYYY-MM-DD')", [$dateString])
                                            ->where('emp_code', $emp_code)
                                            ->get();
                //  Fetch the record for the date available in the database
                if ($attendanceRecords->isNotEmpty()) {
                    // Determine if multiple records exist
                    foreach ($attendanceRecords as $record) {
                        if ($record->att_stat != null) {
                            $isLeave = true;
                            $leaveType = $record->att_stat;
                        }
                    }
                    switch ($leaveType) {
                        case '1':
                            $leaveType = 'Casual Leave';
                            break;
                        case '2':
                            $leaveType = 'Medical Leave';
                            break;
                        case '3':
                            $leaveType = 'Annual Leave';
                            break;
                        case '8':
                            $leaveType = 'Short Leave';
                            break;
                        case '12':
                            $leaveType = 'Outdoor Duty';
                            break;    
                        default:
                            $leaveType = 'Unknown Leave';
                            break;
                    }

                    $timein = $attendanceRecords->min('timein'); 
                    $timeout = $attendanceRecords->max('timeout');

                    // $workedMinutes = 0;
                    $leaveRemark = null;

                    // Check if timein and timeout are within the official working hours
                    $officialStartTime = Carbon::parse($dateString . ' ' . $startTime);
                    $officialEndTime = Carbon::parse($dateString . ' ' . $endTime);
                    
                    $actualIn = Carbon::parse($timein);
                    $actualOut = Carbon::parse($timeout);
                    
                    // Add 10-minute grace to official start
                    $gracePeriodStart = $officialStartTime->copy()->addMinutes(10);
                    
                    // Apply grace period logic
                    if ($actualIn->lessThanOrEqualTo($gracePeriodStart)) {
                        $effectiveIn = $officialStartTime;
                    } else {
                        $effectiveIn = $actualIn;
                    }
                    
                    // Bound the out time
                    $effectiveOut = $actualOut->lessThan($officialEndTime) ? $actualOut : $officialEndTime;
                    
                    // Final worked minutes
                    $workedMinutes = max(0, $effectiveIn->diffInMinutes($effectiveOut));
                    // Calculate minutes worked only if timein and timeout are valid
                    if ($timein && $timeout) {
                        $in = Carbon::parse($timein);
                        $out = Carbon::parse($timeout);
                        $workedMinutes = round($in->diffInMinutes($out));
                    
                        if (!$isLeave) {
                            if ($workedMinutes >= ($totalMins / 2) && $workedMinutes < $totalMins - 120) {
                                $leaveRemark = 'Half Day Eligible';
                            } elseif ($workedMinutes < ($totalMins / 2)) {
                                $leaveRemark = 'Full Day Eligible';
                            }
                        }
                    }
                    
                    $allDates->push([
                        'at_date' => $dateString,
                        'timein' => $timein,
                        'timeout' => $timeout,
                        'is_sunday' => false,
                        'is_holiday' => $isHoliday,
                        'is_leave' => $isLeave,
                        'leave_type' => $isLeave ? $leaveType : null,
                        'worked_minutes' => $workedMinutes,
                        'short_duty_status' => $leaveRemark
                    ]);
                    
                } else {
                    // Manually create a placeholder record for non-leave days
                    $allDates->push([
                        'at_date' => $dateString,
                        'timein' => null,
                        'timeout' => null,
                        'is_sunday' => false,
                        'is_holiday' => $isHoliday,
                        'is_leave' => false
                    ]);
                }
            }
            $tempDate->addDay();
        }

        $allDates = $allDates->sortByDesc('at_date')->values();

        // Get employee details
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