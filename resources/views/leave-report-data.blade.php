@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            {{-- <div class="card mb-4 py-3 border-left-primary">
                <div class="card-body">
                    From: {{ dateFormat($startDate) }} <br>To: {{ dateFormat($endDate) }}
                </div>
            </div> --}}
            <div class="card shadow mt-3 mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Departmental Leave Report (From: {{ dateFormat($startDate) }} To: {{ dateFormat($endDate) }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Casual Leaves</th>
                                    <th>Medical Leaves</th>
                                    <th>Annual Leaves</th>
                                    <th>Late Minutes</th>
                                    <th>Early Minutes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($report as $row)
                                    <tr>
                                        <td>{{ $row['emp_code'] }}</td>
                                        <td>{{ $row['emp_name'] }}</td>
                                        <td class="text-center">{{ $row['leaves']['casual'] }}</td>
                                        <td class="text-center">{{ $row['leaves']['medical'] }}</td>
                                        <td class="text-center">{{ $row['leaves']['annual'] }}</td>
                                        {{-- <td class="text-center fw-bold">
                                            {{ 
                                                $row['leaves']['medical'] 
                                                + $row['leaves']['annual'] 
                                                + $row['leaves']['casual'] 
                                            }}
                                        </td> --}}
                                        <td class="text-center">{{ $row['late_mins'] }}</td>
                                        <td class="text-center">{{ $row['early_mins'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No leave records found for the selected criteria
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection