<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceController extends Controller
{
    public function attendance($emp_code)
    {
        $attendance = Attendance::where('emp_code', $emp_code)->orderBy('at_date', 'desc')->limit(30)->get();
        $attendance->emp_code = $emp_code;
        $employee = Employee::where('emp_code', $emp_code)->first();
        $attendance->emp_name = capitalizeWords($employee->name) ;
        return view('attendance', compact('attendance'));
    }
}
