@extends('jobs.layouts.app')

    @push('styles')
    .profile-small-title {
        width: 400px;
    }
    .profile-small-title.normal-size {
        width: 200px;
    }
    .regular {
        font-weight: 300;
        font-size: 1rem;
        color: #000;
    }
    .profile-title {
        background-color: none;
    }
    .card {
        border-radius: 15px;
    }
    .profile-thumb {
        background-color: #fff;
        border: 2px solid #2196f3;
    }
    .table td, .table td a {
        font-family: var(--heading-font);
        font-weight: 700;
        color: #2196f3;
    }
    @endpush

    @section('content')    
        <div class="container">
            <section id="stats" class="stats section">

                <!-- Section Title -->
                <div class="container section-title">
                    <h2>Applications Summary</h2>
                    <p>Summary of all the job applications receieved through the online job portal of AFMDC.</p>
                </div><!-- End Section Title -->

                <div class="container">

                    <div class="row gy-4">

                    <div class="col-lg-3 col-md-6">
                        <a href="{{route('job-dashboard')}}">
                            <div class="stats-item text-center w-100 h-100">
                                <span>{{$total_jobs}}</span>
                                <p>Total Applications</p>
                            </div>
                        </a>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <a href="{{route('open-jobs')}}">
                            <div class="stats-item text-center w-100 h-100">
                                <span>{{$total_open_jobs}}</span>
                                <p>Open Applications</p>
                            </div>
                        </a>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <a href="{{route('vacancy-jobs')}}">
                            <div class="stats-item text-center w-100 h-100">
                                <span>{{$total_vacancy_jobs}}</span>
                                <p>Vacancy Applications</p>
                            </div>
                        </a>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <a href="{{route('shortlisted')}}">
                            <div class="stats-item text-center w-100 h-100">
                                <span>{{$total_shortlisted_jobs}}</span>
                                <p>Shortlisted Applications</p>
                            </div>
                        </a>
                    </div><!-- End Stats Item -->

                    </div>

                </div>

                </section>
            <div class="row mt-5 mb-5">
                <div class="col-12">
                    
                    <div class="profile-thumb">
                        <div class="profile-title text-center">
                            <h3 class="mb-0">Applications Recieved</h3>
                        </div>
                        <table class="table" id="dataTable">
                            <thead>
                            <tr>
                                <th scope="col">Applications</th>
                                <th scope="col">Position Applied</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach ($vacancy_jobs as $vacancy_job)
                                    @if ($vacancy_job->jobs_count != 0)
                                        <tr>
                                            <td>{{$vacancy_job->jobs_count}}</td>
                                            <td><a href="{{route('designation-jobs',str_replace('/', '-', $vacancy_job->job_description))}}">{{$vacancy_job->job_description}}</a></td>
                                        </tr>
                                    @endif
                                    @continue
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    @endsection    
