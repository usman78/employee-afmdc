<?php

namespace App\Http\Controllers;

use App\Models\Employee; 
use App\Models\LeavesBalance;   
use Illuminate\Support\Facades\Auth;

class LeavesController extends Controller
{
    public function leaves($emp_code)                                                                      
    {
        $authUser = Auth::user();
        if($authUser->employee_code != $emp_code){
            return redirect()->route('home');
        }
        $leaves = LeavesBalance::where('emp_code', $emp_code)->get();
        $leaves->emp_code = $emp_code;
        $employee = Employee::where('emp_code', $emp_code)->first();
        $leaves->emp_name = capitalizeWords($employee->name) ;
        return view('leaves', compact('leaves'))->with('emp_code', $emp_code);
    }
}
