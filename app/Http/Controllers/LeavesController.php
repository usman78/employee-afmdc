<?php

namespace App\Http\Controllers;

use App\Models\Employee; 
use App\Models\LeavesBalance;   
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Leave;
use Illuminate\Support\Facades\Log;
use App\Models\LeaveAuth;
use App\Models\ApprovedLeave;

class LeavesController extends Controller
{
    public function leaves(Request $request, $emp_code)                                                                      
    {
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

        $pendingLeaves = $this->checkPendingLeaves($emp_code);

        // Get employee details
        $employee = Employee::where('emp_code', $emp_code)->first();
        $leaves->emp_name = capitalizeWords($employee->name);
        return view('leaves', compact('leaves', 'pendingLeaves'))->with('emp_code', $emp_code);
    }

    public function empType($emp_code)
    {
        $typeOfEmployee = DB::table('pay_pers')
        ->join('pre_leave_auth', 'pay_pers.emp_code', '=', 'pre_leave_auth.emp_code_l')
        ->where('pay_pers.emp_code', $emp_code)
        ->select('pay_pers.*', 'pre_leave_auth.*')
        ->get();

        $empType = null;
        $deptCode = null;


        foreach($typeOfEmployee as $employee) {
            if ($employee->type == 'R') {
                $empType = 'Regular';
                $deptCode = $employee->dept_code;
                break;
            }
            else{
                $empType = null;
                $deptCode = $employee->dept_code;
                break;
            }
        }
        return [$empType, $deptCode];
    }

    public static function getNextLeaveId()
    {
        $max = DB::table('pre_leave_tran')->max('leave_id');
        log::info('Max leave_id: ' . $max + 1);
        return $max + 1;
    }

    public function checkBalance($empcode, $leave_type, $leave_duration)
    {
        $leave_balance = LeavesBalance::where('emp_code', $empcode)
            ->where('leav_code', $leave_type)
            ->first();

        if (!$leave_balance) {
            return null;
        }    
        
        $balance = $leave_balance->leav_open + $leave_balance->leav_credit - $leave_balance->leav_taken - $leave_balance->leave_encashed;

        $pending = $this->checkPendingLeaves($empcode);
        if ($pending) {
            switch ($leave_type) {
                case 1:
                    $balance -= $pending['casual_leave'];
                    break;
                case 2:
                    $balance -= $pending['medical_leave'];
                    break;
                case 3:
                    $balance -= $pending['annual_leave'];
                    break;
            }
        }
        
        if($balance < $leave_duration){
            return false;
        }
        return true;
    }

    public function checkShortBalance($empcode){
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $leave = Leave::where('emp_code', $empcode)
            ->where('from_date', '>=', $startDate)
            ->where('to_date', '<=', $endDate)
            ->where('leave_code', 8)
            ->get(); 
        if($leave->isEmpty()){
            return true;
        }
        return false;
    }

    public function applyLeaveAdvance($emp_code)
    {
        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        // Get employee details
        $employee = Employee::where('emp_code', $emp_code)->first();

        $pendingLeaves = $this->checkPendingLeaves($emp_code);

        return view('apply-leave-advance', [
            'emp_code' => $emp_code,
            'employee' => $employee,
            'pendingLeaves' => $pendingLeaves,
        ]);
    }

    public function storeLeaveAdvance(Request $request, $emp_code)
    {
        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        // Validate the request
        $request->validate([
            'leave_duration' => 'required|string|in:full,half,short',
            'leave_type' => 'required_if:leave_duration,half,full|integer|in:1,2,3',
            'single_leave_date' => 'required_if:leave_duration,half,short|date',
            'leave_from_date' => 'required_if:leave_duration,full|date',
            'leave_to_date' => 'required_if:leave_duration,full|date',
            'start_time' => 'required_if:leave_duration,short',
            'end_time' => 'required_if:leave_duration,short',
            'leave_interval' => 'required_if:leave_duration,half|integer|in:1,2',
            'reason' => 'required|string|max:255',
        ]);

        // Get the leave type and duration from the request
        $leave_duration = $request->input('leave_duration');

        if ($leave_duration == 'full') {
            
            $range = $request->input('leave_from_date') . ' - ' . $request->input('leave_to_date');
            list($from, $to) = explode(' - ', $range);
            $to = date('d-m-Y', strtotime($to));
            $from = date('d-m-Y', strtotime($from));
            $fromDate = Carbon::parse($from);
            $toDate = Carbon::parse($to);
            $numberOfDays = (int) $fromDate->diffInDays($toDate) + 1;
            if(! $this->checkBalance($emp_code, $request->input('leave_type'), $numberOfDays)){
                return redirect()->back()->with('error', 'You do not have the leave balance.');
            }

            $leave = new Leave();
            $leave->from_date = Carbon::createFromDate($from)->format('Y-m-d');
            $leave->to_date = Carbon::createFromDate($to)->format('Y-m-d');
            $leave->leave_id = self::getNextLeaveId();
            $leave->leave_date = Carbon::today();
            $leave->leave_code = $request->input('leave_type');
            $leave->l_day = $numberOfDays;
            list($employeeType, $deptCode) = $this->empType($emp_code);
            $leave->status = $employeeType == 'Regular' ? '1' : '3';
            $leave->dept_code = $deptCode;
            $leave->user_id = $emp_code;
            $leave->terminal_id = 'online';
            $leave->moddate = now();
            $leave->remark = $request->input('reason');
            $leave->emp_code = $emp_code;
            $leave->leave_date = Carbon::today();

            $check = $this->checkConsecutiveLeave($emp_code, 
                $request->input('leave_type'), 
                $request->input('leave_from_date'), 
                $request->input('leave_to_date'));
            if($check == false){
                return redirect()->back()->with('error', 'You cannot apply for consecutive leaves of different leave types.');
            }
            $leave->save();
            return redirect()->route('attendance', ['emp_code' => $emp_code])->with('success', 'Your leave application has been submitted successfully!');

        } elseif ($leave_duration == 'half') {

            if(! $this->checkBalance($emp_code, $request->input('leave_type'), 0.5)){
                return redirect()->back()->with('error', 'You do not have the leave balance.');
            }
            $leave = new Leave();
            $leave->leave_id = self::getNextLeaveId();
            $leave->leave_date = Carbon::today();
            $leave->emp_code = $emp_code;
            $leave->leave_code = $request->input('leave_type');
            $leaveDate = $request->input('single_leave_date');
            $leaveDate = date('d-m-Y', strtotime($leaveDate));
            $time = Employee::where('emp_code', $emp_code)->first();
            $startTime = Carbon::parse(  "$leaveDate $time->st_time");
            $endTime = Carbon::parse( "$leaveDate $time->end_time");
            $durationMinutes = $startTime->diffInMinutes($endTime);
            $halfDuration = $durationMinutes / 2;
            $midPoint = $startTime->copy()->addMinutes($halfDuration);
            Carbon::parse($midPoint);
            if($request->input('leave_interval') == 1){
                $leave->from_date = $startTime;
                $leave->to_date = $midPoint;
            } else {
                $leave->from_date = $midPoint;
                $leave->to_date = $endTime;
            }
            $leave->l_day = 0.5;
            list($employeeType, $deptCode) = $this->empType($emp_code);
            $leave->status = $employeeType == 'Regular' ? '1' : '3';
            $leave->dept_code = $deptCode;
            $leave->user_id = $emp_code;
            $leave->terminal_id = 'online';
            $leave->moddate = now();
            $leave->remark = $request->input('reason');
            $check = $this->checkConsecutiveLeave($emp_code, 
                $request->input('leave_type'), 
                $request->input('single_leave_date'), 
                $request->input('single_leave_date'));
            if($check == false){
                return redirect()->back()->with('error', 'You cannot apply for consecutive leaves of different leave types.');
            }
            $leave->save();
            return redirect()->route('attendance', ['emp_code' => $emp_code])->with('success', 'Your leave application has been submitted successfully!');
        } elseif ($leave_duration == 'short') {
            if(! $this->checkShortBalance($emp_code)){
                return redirect()->back()->with('error', 'You already availed your short leave.');
            }
            $leave = new Leave();
            $leave->leave_id = self::getNextLeaveId();
            $leave->leave_date = Carbon::today();
            $leave->emp_code = $emp_code;
            $leave->leave_code = 8;
            $fromTime = $request->input('start_time');
            $toTime = $request->input('end_time');
            $leaveDate = $request->input('single_leave_date');
            $fromTime = Carbon::parse("$leaveDate $fromTime");
            $toTime = Carbon::parse("$leaveDate $toTime");
            $leave->from_date = $fromTime;
            $leave->to_date = $toTime;
            $leave->l_day = 1;
            list($employeeType, $deptCode) = $this->empType($emp_code);
            $leave->status = $employeeType == 'Regular' ? '1' : '3';
            $leave->dept_code = $deptCode;
            $leave->user_id = $emp_code;
            $leave->terminal_id = 'online';
            $leave->moddate = now();
            $leave->remark = $request->input('reason');

            $leave->save();
            return redirect()->route('attendance', ['emp_code' => $emp_code])->with('success', 'Your leave application has been submitted successfully!');
        }
        else {
            // Handle invalid leave exception
            return redirect()->back()->with('error', 'Failed to submit leave application due to network.');
        }
    }

    public function leaveApprovals($emp_code)
    {
        // Ensure logged-in user matches the requested employee code
        $authUser = Auth::user();
        if ($authUser->emp_code != $emp_code) {
            return redirect()->route('home');
        }

        // Check if the user is HR
        $hrApprovals = null;
        $hr = $this->identifyHR($emp_code);
        if($hr != null){
            $hrApprovals = Leave::where('status', 5)->get();
        }

        $leavesToApprove = collect();
        $subordinates = LeaveAuth::where('emp_code_a', $emp_code)
            ->where('status', 1)
            ->get();
        foreach($subordinates as $subordinate) {
            if($subordinate->type == 'R'){  
                $leaves = Leave::where('emp_code', $subordinate->emp_code_l)
                    ->where('status', 1)
                    ->get();    
            }
            else if($subordinate->type == 'A'){
                $leaves = Leave::where('emp_code', $subordinate->emp_code_l)
                    ->where('status', 3)
                    ->get();   
            }
            $leavesToApprove = $leavesToApprove->merge($leaves);
        }

        return view('leave-approvals', [
            // 'emp_code' => $emp_code,
            'leaves' => $leavesToApprove,
            'hrApprovals' => $hrApprovals,
            'hr' => $hr,
        ]);
    }
    public function approveLeave(Request $request, $leave_id)
    {
        $user = Auth::user(); 

        $leave = Leave::find($leave_id);
        if (!$leave) {
            return response()->json(['success' => false]);
        }
        $status = $request->input('status');
        if($status == 1){
        Leave::where('leave_id', $leave_id)
            ->update(['status' => 3 , 'user_id_r' => $user->emp_code, 'terminal_id_r' => 'WEB', 'moddate_r' => now()]);
            return response()->json(['success' => true]);
        }
        else if($status == 3){
            Leave::where('leave_id', $leave_id)
            ->update(['status' => 5, 'user_id_h' => $user->emp_code, 'terminal_id_h' => 'WEB', 'moddate_h' => now()]);
            return response()->json(['success' => true]);
        }
        else if($status == 5){

            Leave::where('leave_id', $leave_id)
            ->update(['status' => 7, 'user_id_p' => $user->emp_code, 'terminal_id_p' => 'WEB', 'moddate_p' => now()]);

            $leave = Leave::find($leave_id);

            if (!$leave) {
                return response()->json(['success' => false]);
            }

            $approvedLeave = new ApprovedLeave();
            $approvedLeave->pay_date = $leave->leave_date;
            $approvedLeave->emp_code = $leave->emp_code;
            $approvedLeave->leav_code = $leave->leave_code;
            $approvedLeave->leav_date = $leave->from_date;
            $approvedLeave->days = $leave->l_day;
            $approvedLeave->end_date = $leave->to_date;
            $approvedLeave->auth_emp_code = $leave->user_id_p;
            $approvedLeave->post_flag = 'P';
            $approvedLeave->edit_date = now();
            $approvedLeave->user_name = $leave->user_id_p;
            $approvedLeave->terminal = 'WEB';
            $approvedLeave->pre_leave_id = $leave->leave_id;
            $approvedLeave->leave_nature = 'R';
            $approvedLeave->save();
                
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function approveAll(Request $request)
    {
        $user = Auth::user()->emp_code;
        $leaveIds = $request->input('leave_ids');
        if (!$leaveIds || !is_array($leaveIds)) {
            return response()->json(['success' => false, 'message' => 'No leave IDs provided']);
        }

        foreach ($leaveIds as $leaveId) {
            $leave = Leave::find($leaveId);
            if (!$leave) {
                continue; // Skip if leave not found
            }
            Leave::where('leave_id', $leaveId)
                ->update(['status' => 7, 'user_id_p' => $user, 'terminal_id_p' => 'WEB', 'moddate_p' => now()]);
            
            $approvedLeave = new ApprovedLeave();
            $approvedLeave->pay_date = $leave->leave_date;
            $approvedLeave->emp_code = $leave->emp_code;
            $approvedLeave->leav_code = $leave->leave_code;
            $approvedLeave->leav_date = $leave->from_date;
            $approvedLeave->days = $leave->l_day;
            $approvedLeave->end_date = $leave->to_date;
            $approvedLeave->auth_emp_code = $user;
            $approvedLeave->post_flag = 'P';
            $approvedLeave->edit_date = now();
            $approvedLeave->user_name = $user;
            $approvedLeave->terminal = 'WEB';
            $approvedLeave->pre_leave_id = $leave->leave_id;
            $approvedLeave->leave_nature = 'R';
            $approvedLeave->save();
        }
        return response()->json(['success' => true]);
    }

    public function identifyHR($emp_code)
    {
        $hr = LeaveAuth::where('type', 'P')->get();
        $hrEmpCode = null;
        foreach($hr as $employee) {
            if ($employee->emp_code_a == $emp_code) {
                $hrEmpCode = $employee->emp_code_l;
                break;
            }
        }
        return $hrEmpCode;
    }

    public function checkPendingLeaves($emp_code)
    {
        $leaves = Leave::where('emp_code', $emp_code)
            ->whereIn('status', [1, 3, 5])
            ->get();
        if ($leaves->isEmpty()) {
            return null;
        }
        $pending = [
            'casual_leave' => 0,
            'medical_leave' => 0,
            'annual_leave' => 0,
        ];
    
        foreach ($leaves as $leave) {
            switch ($leave->leave_code) {
                case 1:
                    $pending['casual_leave'] += $leave->l_day;
                    break;
                case 2:
                    $pending['medical_leave'] += $leave->l_day;
                    break;
                case 3:
                    $pending['annual_leave'] += $leave->l_day;
                    break;
            }
        }
    
        return $pending;
    }

    public function checkConsecutiveLeave($emp_code, $leave_code, $from_date, $to_date)
    {
        $from = Carbon::parse($from_date);
        $to = Carbon::parse($to_date);
        $yesterday = Carbon::parse($from)->copy()->subDay();
        $tomorrow = Carbon::parse($to)->copy()->addDay();
        $leave = Leave::where('emp_code', $emp_code)
        ->whereIn('leave_code', [1, 2, 3])  // restricted leave codes
        ->where('leave_code', '!=', $leave_code) // different leave type
        ->where(function ($query) use ($yesterday, $tomorrow) {
            $query->whereDate('to_date', $yesterday) // ends right before new leave
                  ->orWhereDate('from_date', $tomorrow); // starts right after new leave
        })->exists();

        return !$leave;    
    }
    
    public function rejectLeave(Request $request, $leave_id)
    {
        $user = Auth::user()->emp_code; 
        $leave = Leave::find($leave_id);

        if (!$leave) {
            return response()->json(['success' => false]);
        }

        $status = $request->input('status');

        if($status == 1){
            Leave::where('leave_id', $leave_id)
                ->update(['status' => 9, 'user_id_r' => $user, 'terminal_id_r' => 'WEB', 'moddate_r' => now()]);
            return response()->json(['success' => true]);
        }
        else if($status == 3){
            Leave::where('leave_id', $leave_id)
                ->update(['status' => 9, 'user_id_h' => $user, 'terminal_id_h' => 'WEB', 'moddate_h' => now()]);
            return response()->json(['success' => true]);
        }
        else if($status == 5){
            Leave::where('leave_id', $leave_id)
                ->update(['status' => 9, 'user_id_p' => $user, 'terminal_id_p' => 'WEB', 'moddate_p' => now()]);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false]);
    }
}
