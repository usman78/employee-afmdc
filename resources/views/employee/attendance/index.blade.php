{{-- resources/views/employee/attendance/index.blade.php --}}
@extends('layouts.app')

@section('title', 'My Attendance')

@php
    use Carbon\Carbon;

    /**
     * Resolve a display-friendly label and CSS class for each attendance type.
     * Matches the Oracle MV values: P, A, WR, ML, SL, EL, and free-text holidays.
     */
    function attendanceBadge(string $type): array
    {
        $t = trim($type);
        return match(true) {
            $t === 'P'                            => ['label' => 'Present',       'class' => 'badge-present'],
            $t === 'A'                            => ['label' => 'Absent',        'class' => 'badge-absent'],
            $t === 'WR'                           => ['label' => 'Weekly Rest',   'class' => 'badge-rest'],
            $t === 'ML'                           => ['label' => 'Medical Leave', 'class' => 'badge-medical'],
            $t === 'SL'                           => ['label' => 'Short Leave',   'class' => 'badge-short'],
            $t === 'EL'                           => ['label' => 'Earned Leave',  'class' => 'badge-earned'],
            !empty($t)                            => ['label' => $t,              'class' => 'badge-holiday'],
            default                               => ['label' => '—',             'class' => 'badge-unknown'],
        };
    }

    /**
     * Convert decimal minutes to "Xh Ym" string.
     */
    function fmtMinutes(float $minutes): string
    {
        $m = (int) round(abs($minutes));
        if ($m === 0) return '—';
        $h = intdiv($m, 60);
        $rem = $m % 60;
        return $h > 0 ? "{$h}h {$rem}m" : "{$rem}m";
    }

    /**
     * Parse the ST_FROM / END_TO Oracle timestamp strings like
     * "04/06/2026 8:00:00 am" into just "08:00".
     */
    function fmtOracleTime(?string $ts): string
    {
        if (!$ts) return '—';
        try {
            return Carbon::createFromFormat('d/m/Y g:i:s a', trim($ts))->format('H:i');
        } catch (\Throwable) {
            return '—';
        }
    }

    $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
@endphp

@section('content')
<div class="att-wrap">

    {{-- ─── Employee header ─────────────────────────────────────── --}}
    <div class="att-header">
        <div class="att-header__avatar">
            {{ strtoupper(substr($empInfo->name ?? 'E', 0, 1)) }}
        </div>
        <div class="att-header__info">
            <h1 class="att-header__name">{{ $empInfo->name ?? '—' }}</h1>
            <p class="att-header__meta">
                {{ $empInfo->desg_desc ?? '—' }}
                @if(!empty($empInfo->dept_desc))
                    &nbsp;·&nbsp; {{ $empInfo->dept_desc }}
                @endif
                &nbsp;·&nbsp; Emp #{{ $empCode }}
            </p>
        </div>

        {{-- Month / Year filter --}}
        <form method="GET" action="{{ route('attendance.index') }}" class="att-header__filter">
            <select name="month" class="att-select" onchange="this.form.submit()">
                @foreach($availableMonths as $m)
                    <option value="{{ $m['month'] }}"
                            data-year="{{ $m['year'] }}"
                        {{ ($m['month'] == $month && $m['year'] == $year) ? 'selected' : '' }}>
                        {{ $m['label'] }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="year" id="hiddenYear" value="{{ $year }}">
            @if(request()->has('emp_code'))
                <input type="hidden" name="emp_code" value="{{ $empCode }}">
            @endif
        </form>
    </div>

    {{-- ─── Summary cards ───────────────────────────────────────── --}}
    <div class="att-summary">
        <div class="att-card att-card--present">
            <span class="att-card__num">{{ $summary['present'] }}</span>
            <span class="att-card__lbl">Present</span>
        </div>
        <div class="att-card att-card--absent">
            <span class="att-card__num">{{ $summary['absent'] }}</span>
            <span class="att-card__lbl">Absent</span>
        </div>
        <div class="att-card att-card--leave">
            <span class="att-card__num">{{ $summary['medical_leave'] + $summary['short_leave'] + $summary['earned_leave'] }}</span>
            <span class="att-card__lbl">On Leave</span>
        </div>
        <div class="att-card att-card--holiday">
            <span class="att-card__num">{{ $summary['holidays'] }}</span>
            <span class="att-card__lbl">Holidays</span>
        </div>
        <div class="att-card att-card--hours">
            <span class="att-card__num">{{ $summary['total_worked_hrs'] }}<small>h</small></span>
            <span class="att-card__lbl">Hours Worked</span>
        </div>
        <div class="att-card att-card--late">
            <span class="att-card__num">{{ $summary['total_late_min'] }}<small>m</small></span>
            <span class="att-card__lbl">Late (total)</span>
        </div>
        <div class="att-card {{ $summary['deficit_hrs'] > 0 ? 'att-card--deficit' : 'att-card--ok' }}">
            <span class="att-card__num">{{ $summary['deficit_hrs'] }}<small>h</small></span>
            <span class="att-card__lbl">Deficit</span>
        </div>
        <div class="att-card att-card--pct">
            <span class="att-card__num">{{ $summary['attendance_pct'] }}<small>%</small></span>
            <span class="att-card__lbl">Attendance</span>
        </div>
    </div>

    {{-- ─── Detail table ────────────────────────────────────────── --}}
    @if(empty($rows))
        <div class="att-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <p>No attendance records found for {{ $monthName }}.</p>
        </div>
    @else
        <div class="att-table-wrap">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>In</th>
                        <th>Out</th>
                        <th>Worked</th>
                        <th>Required</th>
                        <th>Late</th>
                        <th>Early Exit</th>
                        <th>Deficit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        @php
                            $badge     = attendanceBadge($row->attendance_type ?? '');
                            $type      = trim($row->attendance_type ?? '');
                            $isPresent = in_array($type, ['P', 'SL']);

                            // Parse IN / OUT from the IN_OUT string e.g. "IN : 08:14 OUT : 16:00"
                            // or multiple swipes "IN : 08:11 OUT : 14:03,IN :14:00 OUT : 16:00"
                            $inOut   = trim($row->in_out ?? '');
                            $inTime  = '—';
                            $outTime = '—';
                            if ($inOut && $inOut !== 'IN :  OUT : ' && $inOut !== 'IN : 00:00 OUT : 00:00') {
                                if (preg_match('/IN\s*:\s*(\d{2}:\d{2})/', $inOut, $m)) {
                                    $inTime = $m[1];
                                }
                                // Last OUT in the string (handles multiple swipes)
                                preg_match_all('/OUT\s*:\s*(\d{2}:\d{2})/', $inOut, $outs);
                                if (!empty($outs[1])) {
                                    $outTime = end($outs[1]);
                                }
                            }

                            $workedMin  = (float)($row->total_worked  ?? 0);
                            $reqMin     = (int)  ($row->req_min       ?? 0);
                            $lessMin    = (float)($row->less_minutes  ?? 0);
                            $lateMin    = (float)($row->late_coming   ?? 0);
                            $earlyMin   = (float)($row->early_exit_min ?? 0);
                            $hasDeficit = $lessMin > 0 && $isPresent;

                            try {
                                // Oracle DATE columns arrive via PDO as 'DD-MON-YY' (e.g. '04-JUN-26')
                                // but the NLS session format can vary. We try multiple formats defensively.
                                $raw = trim($row->dated ?? '');
                                foreach (['d/m/Y', 'Y-m-d', 'd-M-y', 'd-M-Y', 'dmY'] as $fmt) {
                                    try {
                                        $dateCarbon = Carbon::createFromFormat($fmt, $raw);
                                        break;
                                    } catch (\Throwable) {
                                        $dateCarbon = null;
                                    }
                                }
                                $dateLabel = $dateCarbon ? $dateCarbon->format('d M Y') : $raw;
                                $isToday   = $dateCarbon?->isToday() ?? false;
                            } catch (\Throwable) {
                                $dateLabel = $row->dated;
                                $isToday   = false;
                            }
                        @endphp
                        <tr class="att-row {{ $isToday ? 'att-row--today' : '' }}">
                            <td class="att-date">
                                {{ $dateLabel }}
                                @if($isToday)<span class="today-pill">Today</span>@endif
                            </td>
                            <td class="att-day">{{ $row->day ?? '—' }}</td>
                            <td>
                                <span class="att-badge {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="att-time {{ $inTime === '—' ? 'text-muted' : '' }}">
                                {{ $inTime }}
                            </td>
                            <td class="att-time {{ $outTime === '—' ? 'text-muted' : '' }}">
                                {{ $outTime }}
                            </td>
                            <td>
                                @if($workedMin > 0)
                                    {{ fmtMinutes($workedMin) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $reqMin > 0 ? fmtMinutes($reqMin) : '—' }}
                            </td>
                            <td class="{{ $lateMin > 30 ? 'text-danger' : ($lateMin > 0 ? 'text-warning' : 'text-muted') }}">
                                {{ $lateMin > 0 ? fmtMinutes($lateMin) : '—' }}
                            </td>
                            <td class="{{ $earlyMin > 0 ? 'text-warning' : 'text-muted' }}">
                                {{ $earlyMin > 0 ? fmtMinutes($earlyMin) : '—' }}
                            </td>
                            <td class="{{ $hasDeficit ? 'text-danger' : 'text-muted' }}">
                                {{ $hasDeficit ? fmtMinutes($lessMin) : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="att-legend">
            <span class="att-badge badge-present">P</span> Present &nbsp;
            <span class="att-badge badge-absent">A</span> Absent &nbsp;
            <span class="att-badge badge-rest">WR</span> Weekly Rest &nbsp;
            <span class="att-badge badge-medical">ML</span> Medical Leave &nbsp;
            <span class="att-badge badge-short">SL</span> Short Leave &nbsp;
            <span class="att-badge badge-earned">EL</span> Earned Leave &nbsp;
            <span class="att-badge badge-holiday">H</span> Holiday
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   Attendance View — Employee Portal
   ═══════════════════════════════════════════════════ */

.att-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem 3rem;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ── Header ───────────────────────────────────────── */
.att-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}

.att-header__avatar {
    width: 52px; height: 52px;
    border-radius: 50%;
    background: #e8f0fe;
    color: #1a56db;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.att-header__info { flex: 1; min-width: 0; }

.att-header__name {
    font-size: 1.2rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 .2rem;
}

.att-header__meta {
    font-size: .82rem;
    color: #6b7280;
    margin: 0;
}

.att-header__filter { margin-left: auto; }

.att-select {
    padding: .4rem .75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: .88rem;
    background: #fff;
    color: #374151;
    cursor: pointer;
}
.att-select:focus { outline: 2px solid #3b82f6; outline-offset: 1px; }

/* ── Summary cards ────────────────────────────────── */
.att-summary {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: .75rem;
    margin-bottom: 1.75rem;
}

.att-card {
    border-radius: 10px;
    padding: .85rem .75rem;
    display: flex; flex-direction: column; align-items: center;
    gap: .2rem;
    border: 1px solid transparent;
}

.att-card__num {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}
.att-card__num small { font-size: .75rem; font-weight: 500; margin-left: 1px; }
.att-card__lbl { font-size: .72rem; font-weight: 500; text-transform: uppercase; letter-spacing: .03em; opacity: .75; }

.att-card--present  { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
.att-card--absent   { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
.att-card--leave    { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
.att-card--holiday  { background:#fefce8; border-color:#fde68a; color:#b45309; }
.att-card--hours    { background:#f5f3ff; border-color:#ddd6fe; color:#6d28d9; }
.att-card--late     { background:#fff7ed; border-color:#fed7aa; color:#c2410c; }
.att-card--deficit  { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
.att-card--ok       { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
.att-card--pct      { background:#f0f9ff; border-color:#bae6fd; color:#0369a1; }

/* ── Table ────────────────────────────────────────── */
.att-table-wrap {
    overflow-x: auto;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.att-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .865rem;
    white-space: nowrap;
}

.att-table thead th {
    background: #f9fafb;
    color: #6b7280;
    font-weight: 600;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: .65rem .85rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.att-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.att-table tbody tr:last-child { border-bottom: none; }
.att-table tbody tr:hover { background: #f9fafb; }

.att-table td { padding: .6rem .85rem; color: #374151; vertical-align: middle; }

.att-row--today { background: #eff6ff !important; }
.att-row--today td { font-weight: 500; }

.att-date { font-weight: 500; color: #111827; }
.att-day  { color: #6b7280; font-size: .82rem; }
.att-time { font-family: 'Courier New', monospace; font-size: .84rem; }

.today-pill {
    display: inline-block;
    margin-left: 6px;
    padding: 1px 6px;
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 600;
    vertical-align: middle;
}

/* ── Badges ───────────────────────────────────────── */
.att-badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .02em;
}
.badge-present  { background: #dcfce7; color: #166534; }
.badge-absent   { background: #fee2e2; color: #991b1b; }
.badge-rest     { background: #f3f4f6; color: #4b5563; }
.badge-medical  { background: #dbeafe; color: #1e40af; }
.badge-short    { background: #fef3c7; color: #92400e; }
.badge-earned   { background: #ede9fe; color: #5b21b6; }
.badge-holiday  { background: #fef9c3; color: #854d0e; }
.badge-unknown  { background: #f3f4f6; color: #6b7280; }

/* ── Utility colours ──────────────────────────────── */
.text-muted   { color: #9ca3af; }
.text-danger  { color: #dc2626; font-weight: 500; }
.text-warning { color: #d97706; }

/* ── Empty state ──────────────────────────────────── */
.att-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #9ca3af;
}
.att-empty svg { margin-bottom: .75rem; opacity: .4; }

/* ── Legend ───────────────────────────────────────── */
.att-legend {
    font-size: .8rem;
    color: #6b7280;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem .15rem;
    align-items: center;
    margin-top: .5rem;
}

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
    .att-summary { grid-template-columns: repeat(4, 1fr); }
    .att-table td, .att-table th { padding: .5rem .6rem; }
}
</style>
@endpush