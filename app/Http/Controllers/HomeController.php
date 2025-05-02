<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveAuth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        // dd('Employee: ' . $user->emp_code);
        $employee = Employee::where('emp_code', $user->emp_code)->first();
        $today = Attendance::where('emp_code', $user->emp_code)->whereDate('at_date', today())->first();

        if($today){
            if($today->timein != null){
                $today->timein = date('H:i', strtotime($today->timein));
            }
            if($today->timeout != null){
                $today->timeout = date('H:i', strtotime($today->timeout));
            }
        }   
        return view('home', compact('employee', 'today'))->with('emp_code', $user);
    }

    public function debug()
    {
        $attendanceRecords = LeaveAuth::where('emp_code_l', '1171')->get();
        return response()->json($attendanceRecords);
    }
}
