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
        $employee = Employee::where('emp_code', $user->employee_code)->first();
        $today = Attendance::where('emp_code', $user->employee_code)->whereDate('at_date', today())->first();
        if($today){
            $today->timein = date('H:i', strtotime($today->timein));
            $today->timeout = date('H:i', strtotime($today->timeout));
        }   
        return view('home', compact('employee', 'today'))->with('emp_code', $user);
    }

    public function debug()
    {
        $user = Employee::first();
        return response()->json($user);
    }
}
