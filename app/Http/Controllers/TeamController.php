<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index($emp_code)
    {
        $teamMembers = Auth::user()->teamMembers->pluck('emp_code_l');   
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
        return view('team.team', compact('emp_code', 'team'));
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
            ->get();

        // Attach the records for the view
        $user->attendance_records = $attendanceRecords;

        // Create a collection with just this one user
        $team = collect([$user]);

        return view('team.team-filter', compact('emp_code', 'team', 'date_range'));
    }
}
