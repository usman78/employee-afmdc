<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        // 🔍 Debug incoming credentials
        $credentials = $request->only('employee_code', 'u_passwd');
        logger()->info('Attempting login with credentials:', $credentials);

        if (Auth::attempt($credentials)) {
            logger()->info('Login successful for user: ' . $credentials['employee_code']);
            return redirect()->intended('/'); // or your desired route
        }

        logger()->warning('Login failed for: ' . $credentials['employee_code']);
        return back()->withErrors([
            'login_error' => 'Invalid employee code or password',
        ]);
    }

    public function username()
    {
        return 'employee_code';
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
