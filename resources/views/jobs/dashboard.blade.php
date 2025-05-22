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
    {{-- .table {
        margin: 0 20px;
    } --}}
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
                <div class="container section-title aos-init aos-animate" data-aos="fade-up">
                    <h2>Applications Summary</h2>
                    <p>Summary of all the job applications receieved through the online job portal of AFMDC.</p>
                </div><!-- End Section Title -->

                <div class="container aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">

                    <div class="row gy-4">

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-item text-center w-100 h-100">
                        <span>{{$total_jobs}}</span>
                        <p>Total Applications</p>
                        </div>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-item text-center w-100 h-100">
                        <span>{{$total_open_jobs}}</span>
                        <p>Open Applications</p>
                        </div>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-item text-center w-100 h-100">
                        <span>{{$total_vacancy_jobs}}</span>
                        <p>Vacancy Applications</p>
                        </div>
                    </div><!-- End Stats Item -->

                    <div class="col-lg-3 col-md-6">
                        <div class="stats-item text-center w-100 h-100">
                        <span>{{$total_shortlisted_jobs}}</span>
                        <p>Shortlisted Applications</p>
                        </div>
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
                                {{-- <th scope="col">Applications</th> --}}
                            </tr>
                            </thead>
                            <tbody>
                                {{-- @php $serialNumber = 1; @endphp --}}
                                @foreach ($open_jobs as $open_job)
                                        <tr>
                                            {{-- <td scope="row">{{$serialNumber}}</td> --}}
                                            <td>{{$open_job->application_count}}</td>
                                            <td><a href="{{route('designation-jobs', str_replace('/', '-', $open_job->desg_short))}}">{{$open_job->desg_short}}</a></td>
                                            {{-- <td>{{$open_job->application_count}}</td> --}}
                                        </tr>
                                        {{-- @php $serialNumber++; @endphp --}}
                                    @if($loop->last)
                                        @foreach ($vacancy_jobs as $vacancy_job)
                                            @if ($vacancy_job->jobs_count != 0)
                                                <tr>
                                                    {{-- <td scope="row">{{$serialNumber}}</td> --}}
                                                    <td>{{$vacancy_job->jobs_count}}</td>
                                                    <td><a href="{{route('designation-jobs',str_replace('/', '-', $vacancy_job->job_description))}}">{{$vacancy_job->job_description}}</a></td>
                                                    {{-- <td>{{$vacancy_job->jobs_count}}</td> --}}
                                                </tr>
                                                {{-- @php $serialNumber++; @endphp --}}
                                            @endif
                                            @continue
                                        @endforeach
                                    @endif    
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
                {{-- <div class="col-6">
                    <div class="card mb-3">
                        <div class="card-body p-3">
                        <div class="chart">
                            <canvas id="myChart" class="chart-canvas" height="207px"></canvas>
                        </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    @endsection    

    @push('scripts')

        {{-- function getTableData() {
            let labels = [];
            let data = [];
        
            // Select table rows
            let rows = document.querySelectorAll("#dataTable tbody tr");
        
            rows.forEach(row => {
                let cells = row.querySelectorAll("td");
                labels.push(cells[0].innerText); // First column as labels
                data.push(parseInt(cells[1].innerText)); // Second column as values
            });
        
            return { labels, data };
        }
        let tableData = getTableData();
    
        const ctx = document.getElementById('myChart').getContext('2d');

        // Function to generate random colors
        function getRandomColor() {
            return `#${Math.floor(Math.random() * 16777215).toString(16)}`;
        }
    
        // Generate an array of colors equal to the number of data points
        var barColors = tableData.data.map(() => getRandomColor());

        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: tableData.labels,
                datasets: [{
                    label: 'Total Applications Against Each Position',
                    data: tableData.data,
                    backgroundColor: barColors,
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        }); --}}

    @endpush