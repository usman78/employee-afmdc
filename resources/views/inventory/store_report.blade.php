@extends('layouts.app')

@push('styles')
<style>
    .report-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #fff;
    }
    .filter-section {
        background-color: #fcfcfd;
        border-radius: 10px;
        padding: 24px;
        border: 1px solid #e9ecef;
    }
    .table-container {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #dee2e6;
    }
    .table thead {
        background-color: #2196f3;
        color: #fff;
    }
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 12px;
    }
    .table td {
        vertical-align: middle;
        font-size: 0.9rem;
        padding: 12px;
    }
    .badge-ack {
        background-color: #28a745;
        color: #fff;
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-no-ack {
        background-color: #dc3545;
        color: #fff;
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-block;
    }
    .btn-generate {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: #fff;
        border: none;
        padding: 10px 24px;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
    }
    .btn-generate:hover {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(33, 150, 243, 0.4);
    }
</style>
@endpush

@section('content')

    <div class="card report-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1 text-primary">Store Issuance Reports</h2>
                <p class="text-muted mb-0">Generate comprehensive reports on inventory movements based on specific criteria.</p>
            </div>
        </div>

        {{-- Display Error Messages --}}
        @if (session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif

        {{-- Report Filtering Form --}}
        <form action="{{ route('inventory.store_report') }}" method="GET" id="reportFilterForm" class="filter-section mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label fw-bold">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label fw-bold">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-3">
                    <label for="department" class="form-label fw-bold">Department:</label>
                    <select name="department" id="department" class="form-control">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ trim($dept->dept_code) }}" {{ $selectedDept == trim($dept->dept_code) ? 'selected' : '' }}>
                                {{ $dept->dept_desc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="acknowledged" class="form-label fw-bold">Acknowledgment Status:</label>
                    <select name="acknowledged" id="acknowledged" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Y" {{ $selectedAcknowledged === 'Y' ? 'selected' : '' }}>Acknowledged</option>
                        <option value="N" {{ $selectedAcknowledged === 'N' ? 'selected' : '' }}>Not Acknowledged</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-end">
                    <button type="submit" id="btnSubmitForm" class="btn btn-generate">
                        <i class="fas fa-sync-alt me-2"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>

        {{-- Report Results --}}
        @if (isset($reportIssues))
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="h4 mb-0 text-dark">Report Results ({{ count($reportIssues) }} items found)</h3>
                </div>
                
                @if (count($reportIssues) > 0)
                    <div class="table-responsive table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Doc No</th>
                                    <th>Date</th>
                                    <th>Recipient</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Value</th>
                                    <th>Status</th>
                                    <th>Acknowledged Date</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportIssues as $issue)
                                    <tr>
                                        <td class="fw-bold">{{ $issue->doc_no }}</td>
                                        <td>{{ $issue->doc_date ? date('d-m-Y', strtotime($issue->doc_date)) : '-' }}</td>
                                        <td>
                                            @if ($issue->emp_name)
                                                {{ $issue->emp_name }} 
                                                <small class="text-muted d-block">Code: {{ trim($issue->receive_by ?? $issue->emp_code) }}</small>
                                            @else
                                                <span class="text-muted">Code: {{ trim($issue->receive_by ?? $issue->emp_code) }}</span>
                                            @endif
                                        </td>
                                        <td><code>{{ $issue->item_code }}</code></td>
                                        <td>{{ $issue->inventory ? $issue->inventory->item_desc : '-' }}</td>
                                        <td class="text-end font-monospace">{{ number_format($issue->qty) }}</td>
                                        <td class="text-end font-monospace">{{ number_format($issue->rate, 2) }}</td>
                                        <td class="text-end font-monospace fw-bold">{{ number_format($issue->value, 2) }}</td>
                                        <td>
                                            @if ($issue->ackn_by_user === 'Y')
                                                <span class="badge-ack"><i class="fas fa-check-circle me-1"></i> Acknowledged</span>
                                            @else
                                                <span class="badge-no-ack"><i class="fas fa-times-circle me-1"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($issue->ackn_by_user === 'Y' && $issue->dated)
                                                {{ date('d-m-Y H:i', strtotime($issue->dated)) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-wrap" style="max-width: 200px;">{{ $issue->remarks ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info py-4 text-center">
                        <i class="fas fa-info-circle fa-2x mb-3 text-secondary d-block"></i>
                        <h5>No record found matching the criteria.</h5>
                        <p class="text-muted mb-0">Try adjusting your filters or date range.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

@endsection