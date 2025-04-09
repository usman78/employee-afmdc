<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

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
            $today->timein = date('H:i', strtotime($today->timein));
            $today->timeout = date('H:i', strtotime($today->timeout));
        }   
        return view('home', compact('employee', 'today'))->with('emp_code', $user);
    }

    public function debug()
    {
        $attendanceRecords = Attendance::where('at_date', '2025-03-12')->where('emp_code', 805)->get();
        return response()->json($attendanceRecords);
    }
}
