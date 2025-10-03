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
@endpush
@section('content')
    <div class="container">
        <div class="row my-5">
            <div class="col-md-12 d-block mx-auto">  
                <div class="portfolio-details">
                    <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                        <h3>Time Table</h3>
                        <p>Time table for the period current month for all classes.</p>
                        <input type="hidden" id="start_date" value="{{ $startDate->toDateString() }}">
                        <input type="hidden" id="end_date" value="{{ $endDate->toDateString() }}">
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
                            <div class="col-md-6">
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
                                <p class="mt-2" id="finalized-message" style="margin-bottom: 0;"></p>
                                <button type="button" class="mt-3 btn btn-success" id="markFinalized" disabled>
                                    <i class="fa-solid fa-check"></i> Mark Finalized
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
@push('scripts')

    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const yearSelect = document.getElementById('yearSelect');
        const programSelect = document.getElementById('programSelect');
        const markFinalizedBtn = document.getElementById("markFinalized");


        // Initialize calendar with no events initially
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                // Use default or selected values
                const start_date = document.getElementById('start_date').value;
                const end_date = document.getElementById('end_date').value;
                const emp_code = document.getElementById('emp_code').value;
                const year = yearSelect.value;
                const program = programSelect.value;
                const url = `/timetables/calendar/events/${year}/${program}?start_date=${start_date}&end_date=${end_date}&emp_code=${emp_code}`;
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
                    ? `<img src="${arg.event.extendedProps.teacher_picture}" alt="Teacher" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; margin-right:5px;">`
                    : '';
                const teacherInfo = `<span style="display: inline-flex; align-items: center;">${teacherPicture}${deliveredBy}</span>`;        
                let hodName = arg.event.extendedProps.hod_name 
                    ? `<small style="color:#2196F3"> HOD: ${arg.event.extendedProps.hod_name}</small>` 
                    : '';
                let hodPicture = arg.event.extendedProps.hod_picture
                    ? `<img src="${arg.event.extendedProps.hod_picture}" alt="HOD" style="width:30px; height:30px; border-radius:50%; vertical-align:middle; margin-right:5px;">`
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
            if(allFinalized) {
                document.getElementById('finalized-message').innerText = "This timetable has been finalized.";
                document.getElementById('markFinalized').disabled = true;
            } else {
                document.getElementById('finalized-message').innerText = "";
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
            const start_date = document.getElementById('start_date').value;
            const end_date = document.getElementById('end_date').value;
            const emp_code = document.getElementById('emp_code').value;
            // Update the calendar's event source URL with new parameters
            calendar.removeAllEvents(); // Clear existing events
            const url = `/timetables/calendar/events/${year}/${program}?start_date=${start_date}&end_date=${end_date}&emp_code=${emp_code}`;
            
            calendar.addEventSource({
                url: url,
                method: 'GET',
                extraParams: {
                    start_date: start_date,
                    end_date: end_date,
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
@endpush