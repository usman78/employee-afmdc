<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee; 
use App\Models\LeavesBalance;   


class LeavesController extends Controller
{
    public function leaves($emp_code)                                                                      
    {
        $leaves = LeavesBalance::where('emp_code', $emp_code)->get();
        $leaves->emp_code = $emp_code;
        $employee = Employee::where('emp_code', $emp_code)->first();
        $leaves->emp_name = capitalizeWords($employee->name) ;
        return view('leaves', compact('leaves'));
    }
}
