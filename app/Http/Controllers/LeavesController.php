<?php

namespace App\Http\Controllers;

use App\Models\Employee; 
use App\Models\LeavesBalance;   
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class LeavesController extends Controller
{
    public function leaves($emp_code)                                                                      
    {
        // Check if user is logged in
        if(!Auth::check()){
            return redirect()->route('login');
        }
        // Check if the logged in user is the same as the user whose leaves are being viewed
        $authUser = Auth::user();
        if($authUser->employee_code != $emp_code){
            return redirect()->route('home');
        }


        // Get leaves balance for the user
        $leaves = LeavesBalance::where('emp_code', $emp_code)
            ->whereIn('leav_code', [1, 2, 3, 8])
            ->get();
        $leaves->emp_code = $emp_code;

        foreach($leaves as $leave) {
            switch ($leave->leav_code) {
                case 1:
                    $leave->leave_type = 'Casual Leave';
                    break;
                case 2:
                    $leave->leave_type = 'Medical Leave';
                    break;
                case 3:
                    $leave->leave_type = 'Annual Leave';
                    break;
                case 8:
                    $leave->leave_type = 'Short Leave';
                    break;
                default:
                    $leave->leave_type = 'Unknown Leave Type';
            }
        }



        $employee = Employee::where('emp_code', $emp_code)->first();
        $leaves->emp_name = capitalizeWords($employee->name) ;
        return view('leaves', compact('leaves'))->with('emp_code', $emp_code);
    }
    public function applyLeave($emp_code, $leave_date)
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

        // Get employee details
        $employee = Attendance::where('emp_code', $emp_code)
            ->where('at_date', $leave_date)->first();

        return view('apply-leave', [
            'emp_code' => $emp_code,
            'emp_name' => $employee ? ucfirst($employee->name) : 'Unknown Employee'
        ]);
    }
}
