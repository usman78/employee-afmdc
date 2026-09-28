<?php

namespace App\Http\Controllers;

use App\Models\ApprovedLeave;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveAuth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

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
        $employeeStatus = employeeStatus($user->emp_code);
        
        // Fetch published notices
        $notices = Notice::where('is_published', true)
                    ->latest()
                    ->take(5)
                    ->get();

        if($today){
            if($today->timein != null){
                $today->timein = date('H:i', strtotime($today->timein));
            }
            if($today->timeout != null){
                $today->timeout = date('H:i', strtotime($today->timeout));
            }
        }   
        return view('home', compact('employee', 'today', 'employeeStatus', 'notices'))->with('emp_code', $user);
    }
    public function changePassword()
    {
        return view('change-password', [
            'passwordChangeRequired' => session('password_change_required'),
            'passwordChangeReason' => session('password_change_reason'),
        ]);
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $user = Auth::user();

        if ($request->current_password != $user->u_passwd) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        if ($request->new_password === $user->u_passwd) {
            return back()->withErrors(['new_password' => 'New password must be different from the current password']);
        }

        $changeReason = $user->u_passwd === '123'
            ? 'default_password'
            : ($this->passwordExpired($user->emp_code) ? 'expired_password' : 'user_changed');

        $user->u_passwd = $request->new_password;
        $user->save();

        DB::table('PASSWORD_CHANGE_TRAILS')->insert([
            'EMP_CODE' => (string) $user->emp_code,
            'CHANGED_BY' => (string) $user->emp_code,
            'CHANGE_REASON' => $changeReason,
            'IP_ADDRESS' => $request->ip(),
            'USER_AGENT' => substr((string) $request->userAgent(), 0, 1000),
            'CHANGED_AT' => now(),
        ]);

        return redirect()->route('home')->with('success', 'Password updated successfully');
    }

    private function passwordExpired(string|int $empCode): bool
    {
        $lastChange = DB::table('PASSWORD_CHANGE_TRAILS')
            ->where('EMP_CODE', (string) $empCode)
            ->max('CHANGED_AT');

        return $lastChange && Carbon::parse($lastChange)->lte(now()->subMonths(2));
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
