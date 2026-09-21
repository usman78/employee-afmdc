<?php

namespace App\Http\Middleware;

use App\Services\ReportAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReportAccess
{
    public function handle(Request $request, Closure $next, string $reportKey): Response
    {
        $user = $request->user();

        $reportAccess = app(ReportAccessService::class);
        $allowed = match ($reportKey) {
            'hr-dashboard' => $user && $reportAccess->allowedAny($user, $reportAccess->groupKeys('HR')),
            'finance-dashboard' => $user && $reportAccess->allowedAny($user, $reportAccess->groupKeys('Finance')),
            'audit-dashboard' => $user && $reportAccess->allowedAny($user, $reportAccess->groupKeys('Audit')),            
            default => $user && $reportAccess->allowed($user, $reportKey),
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
