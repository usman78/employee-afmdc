<?php

namespace App\Http\Controllers;

use App\Models\ApprovedLeave;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveAuth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
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
        $attendanceRecords = Leave::where('emp_code', '1171')->first();
        numberOfLeaveDays($attendanceRecords->from_date, $attendanceRecords->to_date);
        return response()->json(numberOfLeaveDays($attendanceRecords->from_date, $attendanceRecords->to_date));
    }

    public function query(Request $request)
    {
        return view('query');
    }

    public function queryDown(Request $request)
    {
        $query = $request->input('query');
        
        $test = DB::select($query);
        // dd($test);
        // make the json response and send it to the view
        $jsonResponse = json_encode($test);
        // return response()->json($jsonResponse);

        return view('testing', compact('jsonResponse'));
    }
}
