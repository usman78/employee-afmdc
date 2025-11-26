<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index()
    {
        $teamMembers = Auth::user()->teamMembers->pluck('emp_code_l');
        $dgm = Auth::user()->isDGM();   
        $team = collect();
        foreach ($teamMembers as $member) {
            $user = User::where('emp_code', $member)
                ->whereNull('quit_stat')
                ->first();
            if (is_null($user)) {
                continue; // Skip if user is not found or has quit
            }
            $team->push($user);
            $today = Carbon::now();
            $user->attendance_today = $user->attendance()
                ->whereDate('at_date', $today->toDateString())
                ->first();    
        }
        $departments = Department::whereNotIn('dept_code', [61, 60, 64, 48, 54, 11, 13, 17, 18, 19, 31, 32, 58, 65])->get();
        return view('team.team', compact( 'team', 'departments', 'dgm'));
    }

    public function attendanceFilter($emp_code, $date_range)
    {
        $dates = parseDateToRange($date_range);
        $fromDate = $dates['fromDate']->startOfDay();
        $toDate = $dates['toDate'];

        // dd($fromDate);

        // Get the user by emp_code
        $user = User::where('emp_code', $emp_code)
            ->whereNull('quit_stat')
            ->first();

        if (!$user) {
            return back()->with('error', 'Employee not found or has quit.');
        }

        // Get attendance records in date range
        $attendanceRecords = $user->attendance()
            ->whereBetween('at_date', [$fromDate, $toDate])
            ->whereNull('att_stat')
            ->get();

        // Attach the records for the view
        $user->attendance_records = $attendanceRecords;

        // Create a collection with just this one user
        $team = collect([$user]);

        return view('team.team-filter', compact('emp_code', 'team', 'date_range'));
    }
    public function dgmTeamFilter(Request $request)
    {
        $department_id = $request->input('dept_code');
        $emp_code = $request->input('emp_code');
    
        $teamMembersQuery = User::whereNull('quit_stat')->where(function($query) use ($department_id, $emp_code) {
            if ($department_id) {
                $query->where('dept_code', $department_id);
            }
            if ($emp_code) {
                $query->where('emp_code', $emp_code);
            }
        });

        $teamMembers = $teamMembersQuery->get();
        // dd($teamMembers);
        $team = collect();
        foreach ($teamMembers as $user) {
            $team->push($user);
            $today = Carbon::now();
            $user->attendance_today = $user->attendance()
                ->whereDate('at_date', $today->toDateString())
                ->first();    
        }

        return view('team.dgm-filter', compact('team', 'department_id', 'emp_code'));
    }
}
