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
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }
    
        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->employee_code != $emp_code) {
            return redirect()->route('home');
        }
    
        // Get attendance records (excluding Sundays)
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::today();
        $attendanceRecords = Attendance::where('emp_code', $emp_code)
            ->whereBetween('at_date', [$start_date, $end_date])
            ->orderBy('at_date', 'desc')
            ->get()
            ->keyBy(fn ($record) => Carbon::parse($record->at_date)->toDateString()); // Store by date for easy lookup
        Log::info('Attendance Records:', $attendanceRecords->toArray());
        // Generate full list of dates (including Sundays)
        $allDates = collect();
        $tempDate = $start_date->copy();
    
        while ($tempDate->lte($end_date)) {
            $dateString = $tempDate->toDateString(); // Format: YYYY-MM-DD
    
            if ($tempDate->isSunday()) {
                // Manually create a placeholder record for Sundays
                $allDates->push([
                    'at_date' => $dateString,
                    'timein' => null,
                    'timeout' => null,
                    'is_sunday' => true
                ]);
            } else {
                // Use actual attendance record if available
                $attendanceRecord = $attendanceRecords->get($dateString);
                // dd($attendanceRecords->toArray());
                $allDates->push([
                    'at_date' => $dateString,
                    'timein' => optional($attendanceRecord)->timein,
                    'timeout' => optional($attendanceRecord)->timeout,
                    'is_sunday' => false
                ]);
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
