@extends('layouts.app')
@push('styles')
    .fc .fc-daygrid-body-natural .fc-daygrid-day-events {
        cursor: pointer;
    }
    a.fc-daygrid-dot-event {
        white-space: normal;
        display: grid !important;
        padding: 2px 2px;
        justify-content: center;
        margin-bottom: 2px;
    }
    a.fc-daygrid-dot-event.event-delivered {
        background: #e1fffc;
        border: 1px solid #00c4b2;
    }
    a.fc-daygrid-dot-event.event-pending {
        background: #fff3e0;  
        border: 1px solid #ff9800;
    }
    a.event-pending .lecture-status {
        color: crimson;
    }
    .fa-solid.fa-xmark {
        color: crimson;
        font-size: x-large;
    }
    .fa-solid.fa-check {
        font-size: x-large;
    }
    .fc-daygrid-day-events {
        margin-left: 1px;
    }
    .masthead-notice {
        background-color: #d3ff84;
        box-shadow: inset 0 -2px 1px rgba(var(--bs-body-color-rgb), .15), 0 .25rem 1.5rem rgba(var(--bs-body-bg-rgb), .75);
    }
@endpush
@section('content')
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif
        <div class="row my-5">
            <div class="col-md-12 d-block mx-auto">  
                <div class="portfolio-details">
                    <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                        <h3>Time Table</h3>
                        <p>Timetable for the current month or selected date range for all programs.</p>
                        <input type="hidden" id="emp_code" value="{{ $empCode }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <small class="text-muted">Select Year </small>
                                    <span class="text-danger">*</span>
                                    <select class="form-select" id="yearSelect" name="year_id">
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                        <option value="4">Year 4</option>
                                        <option value="5">Year 5</option>
                                    </select>
                                </div>
                            </div>
                            <div   div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <small class="text-muted">Select Program </small>
                                    <span class="text-danger">*</span>
                                    <select class="form-select" id="programSelect" name="program_id">
                                        <option value="MBBS">MBBS</option>
                                        <option value="DPT">DPT</option>
                                        <option value="MIT">MIT</option>
                                        <option value="MLT">MLT</option>
                                        <option value="NUT">NUT</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <small class="text-muted">From Date</small>
                                    <input type="date" class="form-control" id="start_date" value="{{ $startDate->toDateString() }}">  
                                </div> 
                            </div>
                            <div class="col-md-6">
                                <div class="border p-3 rounded">
                                    <small class="text-muted">To Date</small>
                                    <input type="date" class="form-control" id="end_date" value="{{ $endDate->toDateString() }}">
                                </div>
                            </div>    
                        </div>    
                        <div class="row">
                            <div class="col-md-12 mt-3">
                                <button type="button" class="btn btn-primary" id="loadTimetableBtn">
                                    <i class="fas fa-calendar-day"></i> Load Timetable
                                </button>
                                <a href="{{route('timetables.index')}}" class="btn btn-secondary">
                                    <i class="fa-solid fa-arrow-left"></i> Back
                                </a>
                            </div>    
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-12">              
                                <div id="calendar"></div>
                                <p class="d-none align-items-center gap-1 py-2 px-3 me-2 mb-2 mt-3 mb-lg-0 rounded-5 masthead-notice" id="finalized-message" style="margin-bottom: 0;"></p> 
                                <button type="button" class="d-block mt-3 btn btn-success" id="markFinalized" disabled>
                                    <i class="fa-solid fa-check"></i> Mark Finalized
                                </button>
                                <button  id="downloadPdfBtn" class="btn btn-primary mt-3" disabled>
                                    <i class="fa fa-download"></i> Download Timetable (PDF)
                                </button>
                                <span id="pdfStatus" style="display:none; margin-left:10px;">Generating PDF… <small id="pdfProgress"></small></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
@push('cdn-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@endpush
@push('scripts')

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const yearSelect = document.getElementById('yearSelect');
        const programSelect = document.getElementById('programSelect');
        const markFinalizedBtn = document.getElementById("markFinalized");
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const downloadBtn = document.getElementById('downloadPdfBtn');

        // Initialize calendar with no events initially
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                const emp_code = document.getElementById('emp_code').value;
                const year = yearSelect.value;
                const program = programSelect.value;
                const url = `/timetables/calendar/events/${year}/${program}?start_date=${startDate.value}&end_date=${endDate.value}&emp_code=${emp_code}`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => failureCallback(error));
            },
            eventContent: function(arg) {
                // custom HTML
                let icon = arg.event.extendedProps.delivered ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-xmark"></i>';
                const lectureStatus = `<span class="lecture-status" style="display: inline-flex; align-items: center; gap: 10px;">${icon}${arg.event.title}</span>`;
                let deliveredBy = arg.event.extendedProps.delivered_by 
                    ? `<small style="color:gray;"> By: ${arg.event.extendedProps.delivered_by}</small>` 
                    : '';
                let teacherPicture = arg.event.extendedProps.teacher_picture
                    ? `<img src="${arg.event.extendedProps.teacher_picture}" onerror="this.onerror=null; this.src='/img/default-avatar.jpg';" alt="Teacher Profile Picture" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; margin-right:5px;">`
                    : '';
                const teacherInfo = `<span style="display: inline-flex; align-items: center;">${teacherPicture}${deliveredBy}</span>`;        
                let hodName = arg.event.extendedProps.hod_name 
                    ? `<small style="color:#2196F3"> HOD: ${arg.event.extendedProps.hod_name}</small>` 
                    : '';
                let hodPicture = arg.event.extendedProps.hod_picture
                    ? `<img src="${arg.event.extendedProps.hod_picture}" onerror="this.onerror=null; this.src='/img/default-avatar.jpg';" alt="HOD Profile Picture" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; margin-right:5px;">`
                    : '';
                const hodInfo = `<span style="display: inline-flex; align-items: center;">${hodPicture}${hodName}</span>`;            
                return {
                    html: `${hodInfo} ${lectureStatus} ${teacherInfo}`
                };
            },
            eventDidMount: function(info) {
                if (info.event.extendedProps.delivered) {
                    info.el.classList.add('event-delivered');
                } else {
                    info.el.classList.add('event-pending');
                }
            },
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            eventClick: function(info) {
                alert(
                    "Class: " + info.event.title +
                    "\nStart Time: " + info.event.extendedProps.start_time +
                    "\nEnd Time: " + info.event.extendedProps.end_time
                );
            }
        });

        calendar.render();
        // check if all the events are delivered and enable the button
        calendar.on('eventsSet', function() {
            const events = calendar.getEvents();
            const allDelivered = events.length > 0 && events.every(event => event.extendedProps.delivered);
            const allFinalized = events.length > 0 && events.every(event => event.extendedProps.is_finalized);
            if(allDelivered) {
                downloadBtn.disabled = false;
            } else {
                downloadBtn.disabled = true;
            }
            if(allFinalized) {
                document.getElementById('finalized-message').innerText = "This timetable has been already finalized.";
                document.getElementById('finalized-message').classList.remove('d-none');
                document.getElementById('finalized-message').classList.add('d-sm-inline-flex');
                document.getElementById('markFinalized').disabled = true;
            } else {
                document.getElementById('finalized-message').innerText = "";
                document.getElementById('finalized-message').classList.remove('d-sm-inline-flex');
                document.getElementById('finalized-message').classList.add('d-none');
                document.getElementById('markFinalized').disabled = !allDelivered;
            }
        });

        // Add click handler for load button
        document.getElementById('loadTimetableBtn').addEventListener('click', function (e) {
            e.preventDefault(); 
            const year = yearSelect.value;
            const program = programSelect.value;
            if(year === "" || program === ""){
                alert("Please select both Year and Program.");
                return;
            }
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const emp_code = document.getElementById('emp_code').value;
            // Update the calendar's event source URL with new parameters
            calendar.removeAllEvents(); // Clear existing events
            const url = `/timetables/calendar/events/${year}/${program}?start_date=${startDate.value}&end_date=${endDate.value}&emp_code=${emp_code}`;
            console.log(url);
            calendar.addEventSource({
                url: url,
                method: 'GET',
                extraParams: {
                    start_date: startDate,
                    end_date: endDate,
                    emp_code: emp_code
                },
                failure: function() {
                    alert('There was an error while fetching events!');
                }
            });
        });
        // Add click handler for Mark Finalized button
        markFinalizedBtn.addEventListener("click", function() {
            console.log("Mark Finalized button clicked");
            if(!markFinalizedBtn.disabled){  // prevent accidental clicks
                Swal.fire({
                    title: "Do you want to finalize the timetable?",
                    showCancelButton: true,
                    confirmButtonText: "Save"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        const events = calendar.getEvents();
                        const doc_ids = events.map(event => event.extendedProps.doc_id);
                        fetch('/timetables/mark-finalized', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ doc_ids })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success){
                                Swal.fire("Timetable marked as finalized successfully.", "", "success"); 
                                markFinalizedBtn.disabled = true;
                            } else {
                                alert("Error: " + data.message);
                                Swal.fire("An error occurred", "", data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire("An error occurred", "", "An error occurred while marking the timetable as finalized.");
                        });
                    } else if (result.isDenied) {
                        Swal.fire("Timetable finalization cancelled!", "", "info");
                    }
                });
            }
        });
    });
    // download the calendar script
    (async () => {
        const { jsPDF } = window.jspdf; // jsPDF via CDN
        const downloadBtn = document.getElementById('downloadPdfBtn');
        const statusEl = document.getElementById('pdfStatus');
        const progressEl = document.getElementById('pdfProgress');
        const calendarEl = document.getElementById('calendar');

        // Helper: ensure fonts & images are ready before snapshot
        async function waitForResources() {
            if (document.fonts && document.fonts.ready) await document.fonts.ready;
            await new Promise(r => setTimeout(r, 300)); // small delay

            const imgs = Array.from(calendarEl.querySelectorAll('img'));
            await Promise.all(imgs.map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(res => { img.onload = img.onerror = res; });
            }));
        }

        function showStatus(show, text = '') {
            statusEl.style.display = show ? 'inline' : 'none';
            progressEl.textContent = text;
            downloadBtn.disabled = show;
        }

        // Main button click handler
        downloadBtn.addEventListener('click', async () => {
            try {
                showStatus(true, 'Preparing...');
                await waitForResources();

                showStatus(true, 'Rendering screenshot...');
                const scale = 2; // improves quality (2 = 2x resolution)
                const canvas = await html2canvas(calendarEl, {
                    scale,
                    useCORS: true,
                    allowTaint: false,
                    backgroundColor: '#ffffff',
                });

                showStatus(true, 'Building PDF...');

                const imgData = canvas.toDataURL('image/png');
                const imgWidth = canvas.width;
                const imgHeight = canvas.height;

                // Convert px → pt (1 px = 0.75 pt approx at 96 dpi)
                const pdfWidth = imgWidth * 0.75;
                const pdfHeight = imgHeight * 0.75;

                // Choose orientation dynamically based on aspect ratio
                const orientation = pdfWidth > pdfHeight ? 'l' : 'p';

                // Create PDF exactly the size of your div
                const pdf = new jsPDF({
                    orientation: orientation,
                    unit: 'pt',
                    format: [pdfWidth, pdfHeight],
                });

                // Add image to PDF (fit exactly)
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

                showStatus(true, 'Saving PDF...');
                pdf.save('timetable.pdf');

                showStatus(false);
            } catch (err) {
                console.error('PDF generation error:', err);
                alert('Could not generate PDF: ' + (err.message || err));
                showStatus(false);
            }
        });
    })();
@endpush