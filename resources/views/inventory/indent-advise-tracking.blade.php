@extends('layouts.app')
@push('styles')

        body {
            background: #f6f8fc;
            color: #1f2937;
            font-family: "Inter", "Segoe UI", Arial, sans-serif;
        }

        /* -------------------------------------------------
           PAGE HEADER
        ------------------------------------------------- */

        .page-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 24px 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #172554;
            margin-bottom: 3px;
        }

        .page-subtitle {
            color: #7b8495;
            font-size: 14px;
        }

        /* -------------------------------------------------
           CONTENT
        ------------------------------------------------- */

        .main-content {
            padding: 26px 24px;
        }

        /* -------------------------------------------------
           FILTER CARD
        ------------------------------------------------- */

        .filter-card {
            background: #ffffff;
            border: 1px solid #e7eaf0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #697386;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            border-color: #d9deea;
            height: 44px;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.10);
        }

        .btn-primary-custom {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            height: 44px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            padding: 0 18px;
        }

        .btn-primary-custom:hover {
            background: #4338ca;
            color: #ffffff;
        }

        .btn-outline-custom {
            border: 1px solid #d9deea;
            background: #ffffff;
            color: #344054;
            height: 44px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        /* -------------------------------------------------
           SUMMARY CARDS
        ------------------------------------------------- */

        .summary-card {
            background: #ffffff;
            border: 1px solid #e7eaf0;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .summary-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 19px;
            margin-bottom: 12px;
        }

        .icon-blue {
            background: #eef2ff;
            color: #4f46e5;
        }

        .icon-green {
            background: #ecfdf3;
            color: #16a34a;
        }

        .icon-orange {
            background: #fff7ed;
            color: #ea580c;
        }

        .icon-red {
            background: #fef2f2;
            color: #dc2626;
        }

        .summary-value {
            font-size: 22px;
            font-weight: 700;
            color: #172554;
        }

        .summary-label {
            color: #7b8495;
            font-size: 12px;
            margin-top: 2px;
        }

        /* -------------------------------------------------
           REPORT CARD
        ------------------------------------------------- */

        .report-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* -------------------------------------------------
           TABLE
        ------------------------------------------------- */

        .report-table {
            margin-bottom: 0;
            min-width: 1250px;
        }

        .report-table thead th {
            background: #294c9b;
            color: #ffffff;
            border: none;
            font-size: 12px;
            font-weight: 600;
            padding: 15px 12px;
            white-space: nowrap;
        }

        .report-table tbody td {
            padding: 15px 12px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: middle;
            font-size: 13px;
        }

        .main-row:hover {
            background: #fafbff;
        }

        /* -------------------------------------------------
           EMPLOYEE
        ------------------------------------------------- */

        .employee-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .employee-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #eef2ff;
            color: #4f46e5;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .employee-name {
            font-weight: 600;
            color: #1f2937;
        }

        .employee-code {
            color: #98a2b3;
            font-size: 11px;
            margin-top: 2px;
        }

        .designation {
            font-weight: 500;
            color: #475467;
        }

        .department {
            color: #667085;
            font-size: 12px;
        }

        /* -------------------------------------------------
           DATE / SHIFT
        ------------------------------------------------- */

        .date-main {
            font-weight: 600;
            color: #344054;
        }

        .date-year {
            font-size: 11px;
            color: #98a2b3;
            margin-top: 2px;
        }

        .shift-time {
            font-size: 12px;
            line-height: 1.5;
        }

        .shift-muted {
            color: #98a2b3;
        }

        /* -------------------------------------------------
           OT
        ------------------------------------------------- */

        .ot-time {
            font-weight: 700;
            color: #3156c9;
            white-space: nowrap;
        }

        .salary {
            font-weight: 500;
        }

        .claimed {
            font-weight: 600;
            white-space: nowrap;
        }

        /* -------------------------------------------------
           STATUS
        ------------------------------------------------- */

        .status-badge {
            display: inline-flex;
            align-items: center;

            padding: 5px 9px;
            border-radius: 20px;

            font-size: 11px;
            font-weight: 600;
        }

        .status-approved {
            color: #15803d;
            background: #dcfce7;
        }

        .status-pending {
            color: #b45309;
            background: #fef3c7;
        }

        .status-rejected {
            color: #b91c1c;
            background: #fee2e2;
        }

        /* -------------------------------------------------
           APPROVAL ROW
        ------------------------------------------------- */

        .approval-row td {
            padding-top: 8px !important;
            padding-bottom: 12px !important;
            border-bottom: 1px solid #edf0f5;
        }

        .approval-wrapper {
            background: #f8faff;
            border: 1px solid #e5eaf5;
            border-radius: 9px;

            display: flex;
            align-items: center;

            padding: 10px 14px;
            gap: 0;
        }

        .approval-item {
            flex: 1;
            min-width: 180px;

            display: flex;
            align-items: center;

            gap: 9px;

            padding-right: 15px;
            margin-right: 15px;

            border-right: 1px solid #e3e7ef;
        }

        .approval-item:last-child {
            border-right: none;
            margin-right: 0;
        }

        .approval-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #eef2ff;
            color: #4f46e5;

            flex-shrink: 0;
        }

        .approval-label {
            font-size: 11px;
            font-weight: 600;
            color: #475467;
        }

        .approval-value {
            font-size: 11px;
            color: #98a2b3;
            margin-top: 2px;
        }

        /* -------------------------------------------------
           ACTION BUTTON
        ------------------------------------------------- */

        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;

            display: flex;
            align-items: center;
            justify-content: center;

            color: #667085;
            border-radius: 6px;
        }

        .action-btn:hover {
            background: #f2f4f7;
            color: #344054;
        }

        /* -------------------------------------------------
           REPORT FOOTER
        ------------------------------------------------- */

        .report-footer {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;

            border-top: 1px solid #edf0f5;
        }

        .showing-text {
            color: #98a2b3;
            font-size: 12px;
        }

        .pagination .page-link {
            border: none;
            color: #344054;
            width: 34px;
            height: 34px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 7px;
            margin: 0 2px;

            font-size: 12px;
        }

        .pagination .page-item.active .page-link {
            background: #4f46e5;
            color: #ffffff;
        }

        .pagination .page-link:hover {
            background: #eef2ff;
        }

        /* -------------------------------------------------
           RESPONSIVE
        ------------------------------------------------- */

        @media (max-width: 992px) {

            .main-content {
                padding: 18px;
            }

            .page-header {
                padding: 18px;
            }

            .page-title {
                font-size: 23px;
            }

            .report-footer {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }


@endpush
@section('content')
<div class="page-header">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <div class="page-title">
                    Indent To Advice Tracking
                </div>

                <div class="page-subtitle">
                    Track and manage all indent to advice applications
                </div>

            </div>

            {{-- <div class="d-none d-md-flex gap-2">

                <button class="btn btn-outline-custom px-3">

                    <i class="bi bi-download me-1"></i>

                    Export

                    <i class="bi bi-chevron-down ms-2"></i>

                </button>

                <button class="btn btn-primary-custom px-3">

                    <i class="bi bi-download me-1"></i>

                    Download Report

                </button>

            </div> --}}

        </div>

    </div>

</div>
<div class="main-content">

    <div class="container-fluid">


        <!-- =================================================
             FILTERS + SUMMARY
        ================================================== -->

        <div class="row g-3 mb-4">

            <!-- FILTER CARD -->

            <div class="col-xl-12">

                <form id="indentTrackingForm" class="filter-card h-100">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                From Date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="from_po_date"
                                required
                            >

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">
                                To Date
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="to_po_date"
                                required
                            >

                        </div>

                        <div class="col-md-3">

                            <label class="form-label">
                                Item Code
                            </label>

                            <input type="text" name="item_code" class="form-control" placeholder="Enter Item Code" required>

                        </div>

                        <div class="col-md-1 d-flex align-items-end">

                            <button type="submit" class="btn btn-primary w-100" title="View Report">
                                View
                            </button>

                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

    document.getElementById('indentTrackingForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const form = event.currentTarget;
        const fromDate = form.elements.from_po_date.value;
        const toDate = form.elements.to_po_date.value;
        const itemCode = form.elements.item_code.value.trim();

        if (!fromDate || !toDate || !itemCode) {
            form.reportValidity();
            return;
        }

        const formatReportDate = function (dateValue) {
            const date = new Date(`${dateValue}T00:00:00`);
            const month = date.toLocaleString('en-US', { month: 'short' }).toLowerCase();
            const day = String(date.getDate()).padStart(2, '0');

            return `${day}-${month}-${date.getFullYear()}`;
        };

        var reportUrl = new URL('http://110.39.174.203:7777/reports/rwservlet');
        reportUrl.searchParams.set('I_RMS', '');
        reportUrl.searchParams.set('report', 'R:\\Applications\\inventory\\Reports\\r_indent_tracking.rdf');
        reportUrl.searchParams.set('destype', 'cache');
        reportUrl.searchParams.set('desformat', 'pdf');
        reportUrl.searchParams.set('item_code', itemCode);
        reportUrl.searchParams.set('from_po_date', formatReportDate(fromDate));
        reportUrl.searchParams.set('to_po_date', formatReportDate(toDate));

        window.open(reportUrl.toString(), '_blank');
    });

@endpush