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
    a:hover {
        text-decoration: none;
    }
    .profile-thumb.search-jobs {
        padding: 15px;
    }
    @endpush

    @section('content')    
        <div class="container">
            <section id="stats" class="stats section profile-thumb">

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
                <div class="col-md-12">
                    <div class="profile-thumb search-jobs">
                        <h3 class="text-center mb-4">Search Applications</h3>
                        <form method="GET" action="{{ route('jobs-search') }}" class="row g-2 mb-4">
                            <!-- Position -->
                            <div class="col-md-3">
                                <select
                                    name="position"
                                    class="form-control"
                                >
                                    <option value="">Select Position</option>
                                    @foreach ($open_jobs as $open_job)
                                        <option
                                            value="{{ $open_job->desg_short }}"
                                            {{ request('position') == $open_job->desg_short ? 'selected' : '' }}
                                        >
                                            {{ $open_job->desg_short }}
                                        </option>
                                        @if($loop->last)
                                            @foreach ($vacancy_jobs as $vacancy_job)
                                                <option
                                                    value="{{ $vacancy_job->job_description }}"
                                                    {{ request('position') == $vacancy_job->job_description ? 'selected' : '' }}
                                                >
                                                    {{ $vacancy_job->job_description }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <!-- City -->
                            <div class="col-md-3">
                                <input
                                    type="text"
                                    name="city"
                                    class="form-control"
                                    placeholder="City"
                                    value="{{ request('city') }}"
                                >
                            </div>
                            <!-- Salary Min -->
                            <div class="col-md-2">
                                <input
                                    type="number"
                                    name="salary_min"
                                    class="form-control"
                                    placeholder="Min Salary"
                                    value="{{ request('salary_min') }}"
                                >
                            </div>
                            <!-- Salary Max -->
                            <div class="col-md-2">
                                <input
                                    type="number"
                                    name="salary_max"
                                    class="form-control"
                                    placeholder="Max Salary"
                                    value="{{ request('salary_max') }}"
                                >
                            </div>
                            <!-- Buttons -->
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    Search
                                </button>

                                <a href="{{ route('jobs-search') }}" class="btn btn-outline-secondary w-100">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
                                @foreach ($open_jobs as $open_job)
                                        <tr>
                                            <td>{{$open_job->application_count}}</td>
                                            <td><a href="{{route('designation-jobs', str_replace('/', '-', $open_job->desg_short))}}">{{$open_job->desg_short}}</a></td>
                                        </tr>
                                    @if($loop->last)
                                        @foreach ($vacancy_jobs as $vacancy_job)
                                            @if ($vacancy_job->jobs_count != 0)
                                                <tr>
                                                    <td>{{$vacancy_job->jobs_count}}</td>
                                                    <td><a href="{{route('designation-jobs',str_replace('/', '-', $vacancy_job->job_description))}}">{{$vacancy_job->job_description}}</a></td>
                                                </tr>
                                            @endif
                                            @continue
                                        @endforeach
                                    @endif    
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    @endsection    