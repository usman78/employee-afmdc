@extends('layouts.app')

@push('styles')
    .strength-table th,
    .strength-table td {
        vertical-align: top;
    }
    .strength-table .dept-row {
        background: #f5f7fa;
        font-weight: 600;
    }
    .strength-table .totals-row {
        background: #fff3cd;
        font-weight: 700;
    }
    .strength-table .shortage {
        color: #dc3545;
        font-weight: 700;
    }
    .strength-table .excess {
        color: #198754;
        font-weight: 700;
    }
@endpush

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="m-0 font-weight-bold text-primary">Department Strength Report</h5>
                        <div class="text-muted small">As on {{ $report_date }}</div>
                    </div>
                    <a href="{{ route('hr-reports') }}" class="btn btn-outline-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    @if(!isset($grouped) || $grouped->isEmpty())
                        <form action="{{ route('department-strength-report-data') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="dept_code" class="form-label">Department</label>
                                    <select class="form-select" id="dept_code" name="dept_code">
                                        <option value="">-- All Departments --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->dept_code }}" {{ isset($dept_code) && $dept_code == $dept->dept_code ? 'selected' : '' }}>
                                                {{ capitalizeWords($dept->dept_desc) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Generate Report</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="mb-3">
                            <form action="{{ route('department-strength-report-data') }}" method="POST" class="d-flex gap-2">
                                @csrf
                                <select class="form-select w-auto" id="dept_code" name="dept_code">
                                    <option value="">-- All Departments --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->dept_code }}" {{ isset($dept_code) && $dept_code == $dept->dept_code ? 'selected' : '' }}>
                                            {{ capitalizeWords($dept->dept_desc) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                <a href="{{ route('department-strength-report') }}" class="btn btn-secondary btn-sm">Reset</a>
                            </form>
                            <form action="{{ route('department-strength-report-download') }}" method="POST" class="d-inline" target="_blank">
                                @csrf
                                <input type="hidden" name="dept_code" value="{{ isset($dept_code) ? $dept_code : '' }}">
                                <button type="submit" class="btn btn-success btn-sm ms-2 mt-2">
                                    <i class="fas fa-download"></i> Download PDF
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(isset($grouped) && $grouped->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered strength-table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 4%;">#</th>
                                    <th style="width: 20%;">Department</th>
                                    <th style="width: 18%;">Designation</th>
                                    <th style="width: 12%;">Faculty Req.</th>
                                    <th style="width: 12%;">Faculty On Roll</th>
                                    <th style="width: 12%;">Excess / Short</th>
                                    <th>Comments / Faculty Names</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = 1; @endphp
                                @forelse($grouped as $deptCode => $deptData)
                                    @php
                                        $firstRow = $deptData['rows']->first();
                                        $deptName = $firstRow['dept_desc'] ?? '--';
                                        $rowCount = $deptData['rows']->count();
                                    @endphp
                                    @foreach($deptData['rows'] as $index => $row)
                                        <tr>
                                            @if($index === 0)
                                                <td rowspan="{{ $rowCount }}">{{ $sr }}</td>
                                                <td rowspan="{{ $rowCount }}">{{ capitalizeWords($deptName) }}</td>
                                            @endif
                                            <td>{{ $row['desg_short'] }}</td>
                                            <td class="text-center">{{ $row['no_of_vacancy'] }}</td>
                                            <td class="text-center">{{ $row['filled_vacancy'] }}</td>
                                            <td class="text-center">
                                                @if($row['shortage'] > 0)
                                                    <span class="shortage">({{ $row['shortage'] }})</span>
                                                @elseif($row['shortage'] < 0)
                                                    <span class="excess">+{{ abs($row['shortage']) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if(($row['names'] ?? collect())->isNotEmpty())
                                                    {!! ($row['names'] ?? collect())->implode('<br>') !!}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="dept-row">
                                        <td colspan="3" class="text-end">Subtotal</td>
                                        <td class="text-center">{{ $deptData['totals']['required'] }}</td>
                                        <td class="text-center">{{ $deptData['totals']['filled'] }}</td>
                                        <td class="text-center">
                                            @if($deptData['totals']['shortage'] > 0)
                                                <span class="shortage">({{ $deptData['totals']['shortage'] }})</span>
                                            @elseif($deptData['totals']['shortage'] < 0)
                                                <span class="excess">+{{ abs($deptData['totals']['shortage']) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                    @php $sr++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No records found.</td>
                                    </tr>
                                @endforelse
                                @if($grouped->isNotEmpty())
                                    <tr class="totals-row">
                                        <td colspan="3" class="text-end">Grand Total</td>
                                        <td class="text-center">{{ $overall_totals['required'] }}</td>
                                        <td class="text-center">{{ $overall_totals['filled'] }}</td>
                                        <td class="text-center">
                                            @if($overall_totals['shortage'] > 0)
                                                <span class="shortage">({{ $overall_totals['shortage'] }})</span>
                                            @elseif($overall_totals['shortage'] < 0)
                                                <span class="excess">+{{ abs($overall_totals['shortage']) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                            </table>
                        </div>
                        <div class="text-muted small">
                            Shortage is calculated as approved seats minus on-roll seats.
                        </div>
                        <div class="alert alert-info" role="alert">
                            Please select filters and generate the report to view results.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
