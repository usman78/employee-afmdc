<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class HRDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::now();
        $activeEmployeesCount = Employee::where('quit_stat', null)->count();
        $employeesOnLeaveCount = User::whereHas('leaves', function($query) use ($today) {
            $query->whereDate('from_date', '<=', $today)
                  ->whereDate('to_date', '>=', $today);
        })->count();
        
        return response()->json([
            'activeEmployeesCount' => $activeEmployeesCount,
            'employeesOnLeaveCount' => $employeesOnLeaveCount,
        ]);
    }
}
