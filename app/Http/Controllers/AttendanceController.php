<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; 

class AttendanceController extends Controller
{
    public function attendance($emp_code)
    {
        // dd($emp_code);
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->employee_code != $emp_code) {
            return redirect()->route('home');
        }

        // Define national holidays
        $holidays = [
            '2025-03-31', // Eid
            '2025-04-01', // Eid
            '2025-04-02', // Eid
            '2025-05-01', // Labor Day
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
        // dd($allDates);
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

                if ($attendanceRecords->isNotEmpty()) {
                    // Determine if multiple records exist
                    foreach ($attendanceRecords as $record) {
                        if ($record->att_stat != null) {
                            $isLeave = true;
                            $leaveType = $record->att_stat;
                            //break;
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
                        default:
                            $leaveType = 'Unknown Leave';
                            break;
                    }

                    // Pick the earliest time-in and latest time-out (merging records)
                    $timein = $attendanceRecords->min('timein'); 
                    // $timein = $attendanceRecords->whereNull('att_stat')->min('timein'); 
                    $timeout = $attendanceRecords->max('timeout');

                    $allDates->push([
                        'at_date' => $dateString,
                        'timein' => $timein,
                        'timeout' => $timeout,
                        'is_sunday' => false,
                        'is_holiday' => $isHoliday,
                        'is_leave' => $isLeave,
                        'leave_type' => $isLeave ? $leaveType : null
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

        return view('attendance', [
            'attendance' => $allDates,
            'emp_code' => $emp_code,
            'emp_name' => $employee ? ucfirst($employee->name) : 'Unknown Employee'
        ]);
    }


}