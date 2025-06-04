<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;

class TaskController extends Controller
{
    public function index()
    {
        $meeting = Meeting::where('app_emp', auth()->user()->emp_code)
            // ->where('status', '!=', 'C')
            // ->orderBy('meet_date', 'desc')
            ->get();
        return view('tasks.index' , [
            'meetings' => $meeting,
            
        ]);
    }

    public function meetings()
    {
        $meetings = Meeting::where('app_emp', auth()->user()->emp_code)
            ->orderBy('meet_date', 'desc')
            ->with('employee')
            ->get();
        return view('tasks.meeting', [
            'meetings' => $meetings,
        ]);
    }

    public function sops()
    {
        return view('tasks.sops');
    }
}
