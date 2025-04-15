<?php

namespace App\Http\Controllers;

use App\Models\Employee; 
use App\Models\LeavesBalance;   
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Leave;
use Illuminate\Support\Facades\Log;

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
        if($authUser->emp_code != $emp_code){
            return redirect()->route('home');
        }


        // Get leaves balance for the user
        $leaves = LeavesBalance::where('emp_code', $emp_code)
            ->whereIn('leav_code', [1, 2, 3])
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
                default:
                    $leave->leave_type = 'Unknown Leave Type';
            }
        }
        // Get employee details
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
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        // Get employee details
        $employee = Employee::where('emp_code', $emp_code)->first();

        return view('apply-leave', [
            'emp_code' => $emp_code,
            'employee' => $employee,
            'leave_date' => $leave_date,
        ]);
    }

    public function storeLeave(Request $request, $emp_code, $leave_date)
    {

        // dd($request->all());
        // Check if user is logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        $checkResult = $this->checkLeave(
            $emp_code,
            $request->input('leave_type') == 'casual' ? 1 : ($request->input('leave_type') == 'medical' ? 2 : 3),
            $request->input('leave_duration') == 'full' ? 1 : 0.5
        );
        
        if ($checkResult == null) {
            return redirect()->back()->with('error', 'You do not have the leave balance.');
        }        
        // Validate the request
        $request->validate([
            'leave_type' => 'required|string|in:casual,medical,annual',
            'leave_duration' => 'required|string|in:full,half',
            'reason' => 'required|string|max:255',
        ]);
        // dd($request->all());
        // Get the leave type and reason from the request
        $leave_type = $request->input('leave_type');
        $leave_duration = $request->input('leave_duration');
        $reason = $request->input('reason');
        // Get the employee's leave balance 
        // $leave_balance = LeavesBalance::where('emp_code', $emp_code)
        //     ->where('leav_code', $leave_type)
        //     ->first();
        // Check if the leave balance is sufficient
        // if ($leave_balance->balance <= 0) {
        //     return redirect()->back()->with('error', 'Insufficient leave balance.');
        // }
        
        // Check if the leave type is valid
        // $validLeaveTypes = [1, 2, 3]; // Assuming these are the valid leave types
        // if (!in_array($leave_type, $validLeaveTypes)) {
        //     return redirect()->back()->with('error', 'Invalid leave type.');
        // }
        // Check if the reason is provided
        // if (empty($reason)) {
        //     return redirect()->back()->with('error', 'Reason for leave is required.');
        // }
        
        $leave = new Leave();
        $leave->leave_id = self::getNextLeaveId();
        $leave->leave_date = Carbon::today();
        $leave->emp_code = $emp_code;
        $leave->leave_code = $leave_type == 'casual' ? 1 : ($leave_type == 'medical' ? 2 : 3);       
        $leave->from_date = $leave_date;
        $leave->to_date = $leave_date;
        $leave->status = '3';
        $leave->user_id = $emp_code;
        $leave->terminal_id = 'online';
        $leave->moddate = now();
        $leave->remark = $reason;
        $leave->l_day = $leave_duration === 'full' ? 1 : 0.5;

        // dd($request->all());
        
        $leave->save();


        // Update the leave balance
        // DB::table('pay_leave_bal')
        //     ->where('emp_code', $emp_code)
        //     ->where('leav_code', $leave->leave_code)
        //     ->increment('leav_taken', $leave->l_day);

        return redirect()->route('leaves', ['emp_code' => $emp_code])->with('success', 'Leave application submitted successfully.');
    }

    public static function getNextLeaveId()
    {
        $max = DB::table('pre_leave_tran')->max('leave_id');
        log::info('Max leave_id: ' . $max + 1);
        return $max + 1;
    }

    public function checkLeave($empcode, $leave_type, $leave_duration)
    {
        $leave_balance = LeavesBalance::where('emp_code', $empcode)
            ->where('leav_code', $leave_type)
            ->first();
        
        $balance = $leave_balance->leav_open + $leave_balance->leav_credit - $leave_balance->leav_taken - $leave_balance->leave_encashed;
        
        if($balance < $leave_duration){
            return null;
        }
        return true;
    }

}
