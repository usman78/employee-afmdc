<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roster;
use Carbon\Carbon;

class RosterController extends Controller
{
    public function index($empCode, $startDate = null, $endDate = null)
    {
        $emp_code = auth()->user()->emp_code;
        $emp_name = auth()->user()->name;
        $startDate = $startDate ?? Carbon::today()->startOfMonth()->toDateString();
        $endDate = $endDate ?? Carbon::today()->toDateString();
        $attendance = Roster::where('emp_code', $empCode)
            ->whereBetween('dated', [$startDate, $endDate])
            ->get();
        return view('attendance.attendance', compact('emp_code', 'emp_name', 'attendance'));
    }
}
