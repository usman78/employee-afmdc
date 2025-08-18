@extends('jobs.layouts.app')
@push('styles')
    main {
        margin: 50px 0;
    }
    .profile-small-title {
        color: #294a70;
    }
    .services-thumb {
        border: 2px solid var(--border-color);
    }
@endpush
@section('content')
        <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="profile-thumb">
                    <div class="profile-title">
                        <h3 class="mb-0">Application Status</h3>
                    </div>

                    <div class="profile-body">
                        <p>
                            <span class="profile-small-title">Applied For</span>
                            @if($job->designation)
                                <span class="badge badge-pill badge-primary" id="selection-status" style="background-color: #294a70"> {{ $job->designation }} </span>
                            @else
                                <span class="badge badge-pill badge-primary" id="selection-status" style="background-color: #294a70"> {{ $job->vacancy }} </span>    
                            @endif
                        </p>
                        <p>
                            <span class="profile-small-title">Status</span> 
                            @if($job->status == 'C')
                                <span class="badge badge-danger" id="selection-status" style="background-color: crimson"> Cancelled </span>
                            @elseif($job->status == NULL)
                                <span class="badge badge-warning" id="selection-status" style="background-color: dodgerblue"> Pending Review </span>
                            @elseif($job->status == 'S')
                                <span class="badge badge-pill badge-success" id="selection-status" style="background-color: cadetblue"> Shortlisted </span>
                            @endif
                        </p>
                        <p>
                            <span class="profile-small-title">Selection</span> 
                            <span style="display: inline-block;">
                                <select id="selection" class="form-select" aria-label="Default select example">
                                    <option value="" disabled selected>Change the Status</option>
                                    <option value="S">Select the Applicant</option>
                                    <option value="">Keep it in Job Bank</option>
                                    <option value="C">Reject the Applicant</option>
                                </select>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-6 section-title-wrap d-flex justify-content-center align-items-center">
                <h2 class="text-white me-4 mb-0" style="letter-spacing: normal; font-size: x-large;">{{$job->app_name}}</h2>

                <img src="{{asset('applications/').'/'.$job->app_no.'/'.$job->profile_pic}}" class="avatar-image img-fluid" alt="candidate-picture">
            </div>
        </div>  
        <div class="row mt-5">
            <div class="col-lg-6 col-12">
                <div class="services-thumb">
                    <div class="d-flex flex-wrap align-items-center border-bottom mb-4 pb-3">
                        <h3 class="mb-0">Personal Data</h3>
                    </div>
                    <strong class="site-footer-title d-block mb-3">Applicant Name</strong>

                    <p class="mb-2">{{ $job->app_name }}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">Father Name</strong>

                    <p>{{$job->f_name}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">Mobile Number</strong>

                    <p>{{$job->mbl_no}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">Email Address</strong>

                    <p>{{$job->email}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">Date of Birth</strong>

                    <p class="mb-0">{{date('d-m-Y', strtotime($job->dob))}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">CNIC</strong>

                    <p class="mb-0">{{$job->cnic}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">CITY</strong>

                    <p class="mb-0">{{$job->city}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">ADDRESS</strong>

                    <p class="mb-0">{{$job->per_adrr}}</p>

                    <strong class="site-footer-title d-block mt-4 mb-3">Religion</strong>

                    <p>{{$job->app_religion}}</p>
                </div>
            </div>
            <div class="col-lg-6 col-12">
                <div class="services-thumb" style="max-height: 1098px;">
                    <div class="d-flex flex-wrap align-items-center border-bottom mb-4 pb-3">
                        <h3 class="mb-0">Application Data</h3>
                    </div>
                    <strong class="site-footer-title d-block mb-3">Application Number</strong>

                    <p class="mb-2">{{ $job->app_no }}</p>

                    <strong class="site-footer-title d-block mb-3">Application Date</strong>

                    <p class="mb-2">{{ date('d-m-Y', strtotime($job->created_at)) }}</p>

                    <strong class="site-footer-title d-block mb-3">Expected Salary</strong>

                    <p class="mb-2">{{ $job->expt_sal }}</p>

                    <strong class="site-footer-title d-block mb-3">Last Salary</strong>

                    <p class="mb-2">{{ number_format($job->last_sal) }}</p>

                    <strong class="site-footer-title d-block mb-3">PMDC Number</strong>

                    <p class="mb-2">{{$job->pmdc_no ?? 'N/A' }}</p>

                    {{-- <strong class="site-footer-title d-block mb-3">Profile Completion</strong>

                    <p class="mb-2">{{ $job->is_profile_comp == 'Y' ? 'Yes' : 'No' }}</p> --}}

                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h4 class="mt-2 d-block">Applicant Documents</h4>
                    </div>
                    <p class="mb-1" style="display: inline-block;">
                        <a data-cv=true class="custom-btn btn" href="{{ route('download-file', ['id' => $job->app_no , 'fileName' => $job->cv_id]) }}">View CV</a>  
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-file', ['id' => $job->app_no, 'fileName' => $job->cnic_front]) }}" target="_blank">CNIC Front</a>  
                    </p>
                    <p class="mb-1" style="display: inline-block;">
                        <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-file', ['id' => $job->app_no,'fileName' => $job->cnic_back]) }}" target="_blank">CNIC Back</a>
                    </p>
                    <div class="d-flex flex-wrap align-items-center border-top border-bottom mb-4 mt-4">
                        <h4 class="mt-2 d-block">Educational Documents</h4>
                    </div>
                    {{-- @if ($job->education)
                        @foreach ($job->education as $edu)
                            <p class="mb-1" style="display: inline-block;">
                                <a data-gallery="manual" class="custom-btn btn" href="{{ route('download-file',['id' => $edu->app_no,'fileName' => $edu->edu_doc]) }}">{{$edu->edu_dgr_name}} Degree</a>  
                            </p>
                        @endforeach
                    @else 
                        <p class="mb-2">No Record Found.</p>
                    @endif --}}
                    @if ($job->education && count($job->education) > 0)
                        @foreach ($job->education as $edu)
                            <p class="mb-1" style="display: inline-block;">
                                @if ($edu->edu_doc)
                                    <a data-gallery="manual" class="custom-btn btn" 
                                    href="{{ route('download-file', ['id' => $edu->app_no, 'fileName' => $edu->edu_doc]) }}">
                                        {{ $edu->edu_dgr_name }} Degree
                                    </a>
                                @else
                                    <span class="text-muted">{{ $edu->edu_dgr_name }} Degree (But No Document Uploaded)</span>
                                @endif
                            </p>
                        @endforeach
                    @else
                        <p class="mb-2">No Record Found.</p>
                    @endif

                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-auto" style="margin: 0 auto;">
                <a href="{{ route('job-bank') }}" class="custom-btn btn back-btn">Back To Dashboard</a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

    document.addEventListener("DOMContentLoaded", function () {
        const selectElement = document.getElementById("selection");
        const statusLabel = document.getElementById('selection-status');

        selectElement.addEventListener("change", function () {
            if (selectElement.value === 'S') {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to select the applicant!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#294a70",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, confirm!"
                }).then((result) => {
                    if(result.isConfirmed){
                        $.ajax({
                            url: '{{route('change-status', ['app_no' => $job->app_no])}}',
                            type: 'POST',
                            data: {
                                _token: '{{csrf_token()}}',
                                status: 'S'
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Status Changed!",
                                        text: "The applicant is selected!",
                                        icon: "success"
                                    }).then(() => {
                                        window.location.href = "{{ route('job-bank') }}";
                                    });
                                    {{-- statusLabel.innerHTML = 'Shortlisted'; --}}
                                }
                                else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "An error occured while chnaging the status.",
                                        icon: "error"
                                    })
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occured while chnaging the status.",
                                    icon: "error"
                                });
                            }
                        })
                    }
                });
            } 
            else if (selectElement.value === 'C') {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to reject the applicant!",
                    icon: "warning",               
                    showCancelButton: true,
                    confirmButtonColor: "#294a70",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, confirm!"
                }).then((result) => {
                    if(result.isConfirmed){
                        $.ajax({
                            url: '{{route('change-status', ['app_no' => $job->app_no])}}',
                            type: 'POST',
                            data: {
                                _token: '{{csrf_token()}}',
                                status: 'C'
                            },
                            success: function (response) {
                                if(response.success) {
                                    Swal.fire({
                                        title: "Status Changed!",
                                        text: "The applicant has been rejected.",
                                        icon: "success"
                                    }).then(() => {
                                        window.location.href = "{{ route('job-bank') }}";
                                    });
                                    {{-- statusLabel.innerHTML = 'Cancelled'; --}}
                                }
                                else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "An error occured while changing the status.",
                                        icon: "error"
                                    })
                                }
                            },
                            error: function(){
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occured while changing the status.",
                                    icon: "error"
                                })
                            }
                        })
                    }
                });
            } 
            else {
                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to keep the applicant on hold!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#294a70",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, confirm!"
                }).then(result => {
                    if(result.isConfirmed){
                        $.ajax({
                            url: '{{route('change-status', ['app_no' => $job->app_no])}}',
                            type: 'POST',
                            data: {
                                _token: '{{csrf_token()}}',
                                status: ""
                            },
                            success: function (response) {
                                if(response.success){
                                    Swal.fire({
                                        title: "Status Changed!",
                                        text: "The status of the application changed successfully.",
                                        icon: "success"
                                    }).then(() => {
                                        window.location.href = "{{ route('job-bank') }}";
                                    });   
                                    {{-- statusLabel.innerHTML = 'Pending Review'; --}}
                                }
                                else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: "An error occured while changing the status.",
                                        icon: "error"
                                    })
                                } 
                            },
                            error: function(){
                                Swal.fire({
                                    title: "Error!",
                                    text: "An error occured while changing the status.",
                                    icon: "error"
                                })
                            }
                        })
                    }
                });
            }
        });
    });



    $(document).ready(function () {
        $('[data-gallery=manual]').click(function (e) {
            e.preventDefault();

            var items = [];
            var clickedIndex = 0;

            $('[data-gallery=manual]').each(function (index) {
                let src = $(this).attr('href');
                items.push({
                    src: src
                });

                if ($(this).is(e.currentTarget)) {
                    clickedIndex = index;
                }
            });

            if (items.length === 0) {
                console.error('No items found for the gallery.');
                return;
            }

            if (clickedIndex < 0 || clickedIndex >= items.length) {
                console.error('Clicked index is out of bounds.');
                return;
            }

            let viewer = new PhotoViewer(items, {
                index: clickedIndex,
                footerToolbar: [
                    'zoomIn',
                    'zoomOut',
                    'prev',
                    'next',
                    'fullscreen',
                    'download',
                    'close'
                ],
                customButtons: {
                    download: {
                        text: '<i class="bi bi-download"></i>',
                        title: 'Download Image',
                        click: function (context) {
                            // Fallback to options.index if getIndex is not available
                            const currentIndex = context.index ?? context.options.index;
                            const currentItem = items[currentIndex];

                            if (!currentItem) {
                                console.error('Invalid item at index:', currentIndex);
                                return;
                            }

                            const imageUrl = currentItem.src;

                            const a = document.createElement('a');
                            a.href = imageUrl;
                            a.download = imageUrl.split('/').pop();
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                    }
                }
            });
        });
    });

    $('[data-cv=true]').click(function (e) {
        e.preventDefault();
        const pdfUrl = $(this).attr('href');
        window.open(pdfUrl);
    });


@endpush

