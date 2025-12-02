@extends('layouts.app')
@push('styles')
    a#view-button {
        display: inline-block;
    }

    a.services-price.view-button {
        display: inline-block;
    }
    
    .dt-layout-row:first-child {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        padding-top: 20px;
    }

    .dt-layout-cell.dt-layout-start {
        display: none !important;
    }

    .dt-layout-cell.dt-layout-end {
        margin-bottom: 20px;
        margin-left: 5px;
        text-align: center;
    }

    button.dt-paging-button {
        border: 1px solid #294a70;
        margin: 0 5px;
        border-radius: 5px;
        color: #294a70;
        padding: 5px 15px;
    }

    button.dt-paging-button:hover {
        background-color: #294a70;
        color: #fff;
    }

    input#dt-search-0 {
        margin-left: 25px;
        border-radius: 5px;
        border: 1px solid #294a70;
        padding: 5px;
    }

    input#dt-search-0:focus-visible {
        outline: none;
    }
@endpush
@section('content')
    <div class="container">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="profile-thumb mt-5 mb-5">
                    <div class="profile-title text-center">
                        <h4 class="mb-0">Admission Applications</h4>
                    </div>
                    <div class="profile-body">
                        <table id="applicants-data" class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Applicant Name</th>
                                <th scope="col">Program Applied</th>
                                <th scope="col">Application Date</th>
                                <th scope="col">View Application</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($admissions as $admission)
                                    <tr>
                                        <td>{{ $admission->adm_applicant_id }}</td>
                                        <td scope="row">{{$admission->user->name}}</td>
                                        <td>{{ getProgramName($admission->program_id) }}</td>
                                        <td>{{date('d-m-Y', strtotime($admission->created_at))}}</td>                                        
                                        <td>
                                            <a href="{{route('applicant', $admission->adm_applicant_id)}}" class="btn btn-small btn-primary services-price view-button" id="view-button">
                                                <div class="services-price-wrap ms-auto">
                                                    <p class="services-price-text mb-0">View</p>
                                                    <div class="services-price-overlay"></div>
                                                </div>
                                            </a>
                                            <a href="{{ route('preview-admission', $admission->adm_applicant_id) }}" class="btn btn-small btn-secondary services-price view-button">
                                                <div class="services-price-wrap ms-auto">
                                                    <p class="services-price-text mb-0">Download</p>
                                                    <div class="services-price-overlay"></div>
                                                </div>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 
@push('cdn-scripts')
    <script src="{{asset('js/DataTables.js')}}"></script>
@endpush
@push('scripts')
    let table = $('#applicants-data').DataTable({
        order: 'desc',
    });
@endpush    