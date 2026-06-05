<?php

namespace App\Http\Controllers;

use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class RosterController extends Controller
{
    /**
     * Display the attendance view for the authenticated employee,
     * or for a specific emp_code (admin/HR use).
     */
    public function index(Request $request)
    {
        // Resolve employee code: from query param (admin) or auth user
        $empCode = $request->query('emp_code', auth()->user()->emp_code ?? null);
 
        if (!$empCode) {
            abort(403, 'Employee code not found.');
        }
 
        // Month/year filter — defaults to current month
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year',  now()->year);
 
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();
 
        // Query the materialized view
        $rows = DB::select("
            SELECT
                emp_code,
                name,
                dated,
                join_date,
                s_d_w,
                day,
                in_out,
                leave_from,
                leave_minutes,
                attendance_type,
                req_min,
                total_worked,
                less_minutes,
                twh,
                st_from,
                end_to,
                h_interval,
                twh_p,
                dept_code,
                dept_desc,
                desg_desc,
                late_coming,
                early_exit_min
            FROM mv_employee_monthly_attendance
            WHERE emp_code = :emp_code
              AND dated BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD')
                            AND TO_DATE(:end_date,   'YYYY-MM-DD')
            ORDER BY dated DESC
        ", [
            'emp_code'   => $empCode,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
        ]);
 
        if (empty($rows)) {
            // Try without date filter to at least get employee info
            $empInfo = DB::selectOne("
                SELECT emp_code, name, dept_desc, desg_desc, join_date
                FROM mv_employee_monthly_attendance
                WHERE emp_code = :emp_code
                  AND rownum = 1
            ", ['emp_code' => $empCode]);
        } else {
            $empInfo = $rows[0];
        }
 
        // ── Summary counts ───────────────────────────────────────────────
        $summary = $this->buildSummary($rows);
 
        // ── Available months for the filter dropdown ─────────────────────
        $availableMonths = $this->getAvailableMonths($empCode);
 
        return view('employee.attendance.index', compact(
            'rows',
            'empInfo',
            'summary',
            'month',
            'year',
            'availableMonths',
            'empCode'
        ));
    }
 
    // ────────────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────────────
 
    /**
     * Attendance type meanings:
     *   P   = Present
     *   A   = Absent
     *   WR  = Weekly Rest (off day)
     *   ML  = Medical Leave
     *   SL  = Short Leave
     *   EL  = Earned/Annual Leave
     *   Holiday labels (e.g. "Eid Ul Adha Holiday", "Labour Day") = Public Holiday
     */
    private function buildSummary(array $rows): array
    {
        $present        = 0;
        $absent         = 0;
        $weeklyRest     = 0;
        $medicalLeave   = 0;
        $shortLeave     = 0;
        $earnedLeave    = 0;
        $holidays       = 0;
        $totalLateMin   = 0;
        $totalEarlyExit = 0;
        $totalWorkedMin = 0;
        $totalReqMin    = 0;
 
        foreach ($rows as $row) {
            $type = trim($row->attendance_type ?? '');
 
            switch ($type) {
                case 'P':  $present++;      break;
                case 'A':  $absent++;       break;
                case 'WR': $weeklyRest++;   break;
                case 'ML': $medicalLeave++; break;
                case 'SL': $shortLeave++;   break;
                case 'EL': $earnedLeave++;  break;
                default:
                    // Named holidays ("Eid Ul Adha Holiday", "Labour Day", etc.)
                    if (!empty($type)) {
                        $holidays++;
                    }
                    break;
            }
 
            $totalLateMin   += (float) ($row->late_coming   ?? 0);
            $totalEarlyExit += (float) ($row->early_exit_min ?? 0);
            $totalWorkedMin += (float) ($row->total_worked  ?? 0);
            $totalReqMin    += (int)   ($row->req_min       ?? 0);
        }
 
        $deficitMin = max(0, $totalReqMin - $totalWorkedMin);
 
        return [
            'present'          => $present,
            'absent'           => $absent,
            'weekly_rest'      => $weeklyRest,
            'medical_leave'    => $medicalLeave,
            'short_leave'      => $shortLeave,
            'earned_leave'     => $earnedLeave,
            'holidays'         => $holidays,
            'total_late_min'   => round($totalLateMin),
            'total_early_exit' => round($totalEarlyExit),
            'total_worked_hrs' => round($totalWorkedMin / 60, 1),
            'total_req_hrs'    => round($totalReqMin    / 60, 1),
            'deficit_hrs'      => round($deficitMin      / 60, 1),
            'attendance_pct'   => $totalReqMin > 0
                                    ? round(($totalWorkedMin / $totalReqMin) * 100, 1)
                                    : 0,
        ];
    }
 
    /**
     * Returns a list of distinct year-month values the employee has records for,
     * so we can populate the month/year filter dropdown.
     */
    private function getAvailableMonths(int $empCode): array
    {
        $results = DB::select("
            SELECT DISTINCT
                TO_CHAR(dated, 'YYYY') AS yr,
                TO_CHAR(dated, 'MM')   AS mo,
                TO_CHAR(dated, 'Month YYYY') AS label
            FROM mv_employee_monthly_attendance
            WHERE emp_code = :emp_code
            ORDER BY TO_CHAR(dated, 'YYYY') DESC,
                     TO_CHAR(dated, 'MM')   DESC
        ", ['emp_code' => $empCode]);
 
        return array_map(fn($r) => [
            'year'  => (int) $r->yr,
            'month' => (int) $r->mo,
            'label' => trim($r->label),
        ], $results);
    }
}
