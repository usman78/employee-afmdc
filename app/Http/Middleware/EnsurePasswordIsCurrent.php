<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('change-password', 'update-password', 'logout')) {
            return $next($request);
        }

        $reason = $this->passwordChangeReason($user, $request);

        if ($reason !== null) {
            return redirect()
                ->route('change-password')
                ->with('password_change_required', true)
                ->with('password_change_reason', $reason);
        }

        return $next($request);
    }

    private function passwordChangeReason($user, Request $request): ?string
    {
        if ($user->u_passwd === '123') {
            return 'You are using a very weak password. Please set a new password before continuing.';
        }

        $lastChange = DB::table('PASSWORD_CHANGE_TRAILS')
            ->where('EMP_CODE', (string) $user->emp_code)
            ->max('CHANGED_AT');

        if (! $lastChange) {
            DB::table('PASSWORD_CHANGE_TRAILS')->insert([
                'EMP_CODE' => (string) $user->emp_code,
                'CHANGED_BY' => (string) $user->emp_code,
                'CHANGE_REASON' => $this->meetsPasswordPolicy($user->u_passwd)
                    ? 'baseline_existing_password'
                    : 'baseline_weak_password',
                'IP_ADDRESS' => $request->ip(),
                'USER_AGENT' => substr((string) $request->userAgent(), 0, 1000),
                'CHANGED_AT' => now(),
            ]);

            return null;
        }

        if ($lastChange && Carbon::parse($lastChange)->lte(now()->subMonths(2))) {
            return 'Your password is older than two months. Please change it before continuing.';
        }

        return null;
    }

    private function meetsPasswordPolicy(?string $password): bool
    {
        return is_string($password)
            && strlen($password) >= 8
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/\d/', $password) === 1;
    }
}
