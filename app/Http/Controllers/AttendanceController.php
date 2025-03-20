<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 

class AttendanceController extends Controller
{
    public function attendance($emp_code)
    {
        $authUser = Auth::user();
        // Log::info('authUser: '.$authUser->employee_code);
        if($authUser->employee_code != $emp_code){
            return redirect()->route('home');
        }
        $attendance = Attendance::where('emp_code', $emp_code)->orderBy('at_date', 'desc')->limit(30)->get();
        $attendance->emp_code = $emp_code;
        $employee = Employee::where('emp_code', $emp_code)->first();
        $attendance->emp_name = capitalizeWords($employee->name) ;
        return view('attendance', compact('attendance'))->with('emp_code', $emp_code);
    }
}
