@extends('jobs.layouts.app')
@push('styles')
    .profile-thumb {
        box-shadow: 0px 5px 90px 0px rgba(0, 0, 0, 0.1);
    }
@endpush
@section('content')
    <div class="container">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="profile-thumb mt-5 mb-5">
                    <div class="profile-title text-center">
                        <h4 class="mb-0">All Job Applications</h4>
                    </div>
                    <div class="profile-body">
                        <table id="applicants-data" class="table table-hover">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Applicant Name</th>
                                <th scope="col">Position Applied</th>
                                <th scope="col">Application Date</th>
                                <th scope="col">Profile Completion</th>
                                <th scope="col">View Application</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobs as $job)
                                    <tr>
                                        <td scope="row">{{$job->app_no}}</td>
                                        <td>{{$job->app_name}}</td>
                                        <td>
                                            @if ($job->designation?->desg_short != null)
                                            {{$job->designation->desg_short}} 
                                            @elseif($job->vacancy->job_description != null)
                                                {{$job->vacancy->job_description}}
                                            @else 
                                                N/A  
                                            @endif
                                        </td>
                                        <td>{{date('d-m-Y', strtotime($job->created_at))}}</td>
                                        @if ($job->is_profile_comp == 'N')
                                            <td><span class="badge bg-warning text-dark">No</span></td> 
                                            <td>
                                                <a href="{{route('profile', $job->app_no)}}" class="services-price view-button" id="view-button">
                                                    <div class="services-price-wrap ms-auto">
                                                        <p class="services-price-text mb-0">View</p>
                                                        <div class="services-price-overlay"></div>
                                                    </div>
                                                </a>
                                            </td>
                                        @else
                                            <td><span class="badge bg-success">Yes</span></td>
                                            <td>
                                                <a href="{{route('profile', $job->app_no)}}" class="services-price">
                                                    <div class="services-price-wrap ms-auto">
                                                        <p class="services-price-text mb-0">View</p>
                                                        <div class="services-price-overlay"></div>
                                                    </div>
                                                </a>
                                            </td>
                                        @endif

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
@push('scripts')




    $(document).ready(function () {
        const viewBtn = document.getElementById('view-button');

        document.querySelectorAll('.view-button').forEach(function (viewBtn){
            viewBtn.addEventListener('click', function (event){
                event.preventDefault();
                Swal.fire({
                    title: "Incomplete Profile",
                    text: "Are you sure you want to open an incomplete profile?",
                    icon: "warning",
                    showCancelButton: true
                }).then((result) => {
                    if (result.isConfirmed){
                        window.location.href = viewBtn.href;
                    }
                })
            });
        });
        let table = $('#applicants-data').DataTable({
            responsive: true,
            order: 'desc',
            paging: true,
            searching: true, // Ensure searching is enabled
            info: true,
        });

        // Check if the search event is being triggered
        $('#dt-search-0').on('keyup', function () {
            console.log("Search Input Value:", this.value);
            table.search(this.value).draw();
        });
    });

@endpush       