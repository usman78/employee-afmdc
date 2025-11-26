@extends('layouts.app')
@push('cdn-styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />    
@endpush
@push('styles')

table {
  table-layout: fixed;   /* 1️⃣ Prevents auto-expanding to fit content */
  width: 100%;           /* 2️⃣ Needed when using fixed layout */
}

td, th {
  overflow: hidden;      /* 3️⃣ Hide overflowing text */
  white-space: nowrap;   /* 4️⃣ Keep text on one line */
  text-overflow: ellipsis; /* 5️⃣ Show "..." */
}
th.subject-title, td.subject-title {
  width: 155px; /* Set your desired width */
}
th.group-title, td.group-title {
    width: 73px;
}
th.subject-id, td.subject-id {
    width: 150px;
}
th.action-btn, td.action-btn {
    width: 82px;
}
th.time, td.time {
    width: 125px;
}
th.topic, td.topic {
    width: 170px;
}
th.date, td.date {
    width: 100px;
}
th.period-type {
    width: 151px;
}

@media (min-width: 576px) {
    .container {
        max-width: 100%;
    }
}
@media (min-width: 768px) {
    .container {
        max-width: 100%;
    }
}
@media (min-width: 992px) {
    .container {
        max-width: 100%;
    }
}
@media (min-width: 1200px) {
    .container {
        max-width: 100%;
    }
}
@media (min-width: 1400px) {
    .container {
        max-width: 100%;
    }
}
    
@endpush
@section('content')
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-12 d-block mx-auto">  
                <div class="portfolio-details">
                    <div class="portfolio-info aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                        <h3>New Time Table</h3>
                        <p>You are creating the new timetable for the below period.</p>
                        <div class="row mb-3 g-3 shadow-sm p-4 rounded bg-white">
                            <div class="col-md-3">
                                <div class="border p-3 rounded bg-secondary bg-gradient">
                                    <small class="text-white">From Date</small>
                                    <div class="fw-bold text-white">{{dateDayMonthFormat($from_date)}}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border p-3 rounded bg-secondary bg-gradient">
                                    <small class="text-white">To Date</small>
                                    <div class="fw-bold text-white">{{dateDayMonthFormat($to_date)}}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border p-3 rounded bg-secondary bg-gradient">
                                    <small class="text-white">Class ID</small>
                                    <div class="fw-bold text-white">{{$classId}}</div>
                                </div> 
                            </div>
                            <div class="col-md-3">
                                <div class="border p-3 rounded bg-secondary bg-gradient">
                                    <small class="text-white">Session Year</small>
                                    <div class="fw-bold text-white">{{$sessionYear}}</div>
                                </div> 
                            </div>
                        </div>

                        <form id="timetable-form" method="post" action="{{route('timetables.store')}}">
                            @csrf
                            <input type="hidden" name="to_date" value="{{$to_date}}">
                            <table class="table table-bordered" id="timetable-table">
                                <thead>
                                    <tr>
                                        <th class="date">Date</th>
                                        <th class="d-none">Day</th>
                                        <th class="d-none">Year ID</th>
                                        <th class="d-none">Class ID</th>
                                        <th class="group-title">Group</th>
                                        <th class="subject-id">Subject ID</th>
                                        <th class="subject-title">Subject Title</th>
                                        <th class="topic">Topic</th>
                                        <th class="period-type">Period Type</th>
                                        <th class="min-width-hod-class">HOD</th>
                                        <th class="time">Start Time</th>
                                        <th class="time">End Time</th>
                                        <th class="action-btn">Add</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach ($dayMap as $date => $dayKey)
                                    @if (isset($timetable[$dayKey]))
                                        @foreach ($timetable[$dayKey] as $record)
                                            <tr class="table-danger">
                                                <td class="date">{{ \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('D, d-M') }}</td>
                                                <td class="d-none">{{ $record->p_day }}</td>
                                                <td class="d-none">{{ $record->year_id }}</td>
                                                <td class="d-none">{{ $record->class_id }}</td>
                                                <td class="group-title">{{ $record->group_title }}</td>
                                                <td class="subject-id">{{ $record->subject_id }}</td>
                                                <td class="subject-title">{{ $record->subject->title }}</td>
                                                <td class="topic">Add</td>
                                                <td>{{ $record->period_type }}</td>
                                                <td class="hod">HOD</td>
                                                <td class="time">{{ $record->start_time }}</td>
                                                <td class="time">{{ $record->end_time }}</td>
                                                <td class="action-btn">
                                                    <button type="button" class="btn btn-secondary btn-sm add-more-btn">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="fas fa-download"></i> Save Entries
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('cdn-scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>    
@endpush
@push('scripts')
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('timetable-table');
        const timetableForm = document.getElementById('timetable-form');
        table.addEventListener('click', function (e) {
            // ADD MORE button
            if (e.target.closest('.add-more-btn')) {
                const button = e.target.closest('.add-more-btn');
                const currentRow = button.closest('tr');

                // Identify original row
                let originalRow = currentRow;
                if (currentRow.dataset.originalId) {
                    // If this is a clone, get its original
                    originalRow = table.querySelector(`tr[data-row-id="${currentRow.dataset.originalId}"]`);
                }

                // Give original a unique ID if not already set
                if (!originalRow.dataset.rowId) {
                    originalRow.dataset.rowId = Date.now();
                }

                // Clone the row
                const clone = currentRow.cloneNode(true);
                clone.classList.add('table-success');
                clone.classList.remove('table-danger');
                clone.dataset.originalId = originalRow.dataset.rowId; // link to original
                const cells = clone.children;

                // Helper: Get value from cell
                const getCellValue = (cell) => {
                    const input = cell.querySelector('input, select, textarea');
                    return input ? input.value.trim() : cell.innerText.trim();
                };

                // DATE
                const dateVal = getCellValue(currentRow.children[0]);
                cells[0].innerHTML = `<input type="hidden" name="date[]" value="${dateVal}">${dateVal}`;

                // DAY
                const dayVal = getCellValue(currentRow.children[1]);
                cells[1].innerHTML = `<input type="hidden" name="day[]" value="${dayVal}">${dayVal}`;

                // YEAR ID
                const yearVal = getCellValue(currentRow.children[2]);
                cells[2].innerHTML = `<input type="hidden" name="year_id[]" value="${yearVal}">${yearVal}`;

                // CLASS ID
                const classVal = getCellValue(currentRow.children[3]);
                cells[3].innerHTML = `<input type="hidden" name="class_id[]" value="${classVal}">${classVal}`;

                // GROUP
                const groupVal = getCellValue(currentRow.children[4]);
                cells[4].innerHTML = `<select name="group[]" class="form-select form-select-sm">
                                            <option value="">Group</option>
                                            <option value="A" ${groupVal == "A" ? 'selected' : ''}>A</option>
                                            <option value="B" ${groupVal == "B" ? 'selected' : ''}>B</option>
                                            <option value="C" ${groupVal == "C" ? 'selected' : ''}>C</option>
                                            <option value="D" ${groupVal == "D" ? 'selected' : ''}>D</option>
                                            <option value="G" ${groupVal == "G" ? 'selected' : ''}>G</option>
                                            <option value="H" ${groupVal == "H" ? 'selected' : ''}>H</option>     
                                    </select>`;
                // Subject ID
                const subjectVal = getCellValue(currentRow.children[5]);
                cells[5].innerHTML = `<select name="subject_id[]" class="form-select form-select-sm js-example-basic-single" required>
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject }}" ${subjectVal == "{{ $subject }}" ? 'selected' : ''}>{{ $subject }}</option>
                                            @endforeach
                                    </select>`;

                // SUBJECT TITLE
                const subjTitleVal = getCellValue(currentRow.children[6]);
                cells[6].innerHTML = `<input type="text" name="subject_title[]" class="form-control form-control-sm" value="${subjTitleVal}" readonly>`;

                // Topic
                cells[7].innerHTML = `<button type="button" class="btn btn-primary btn-sm add-topic-btn" style="width: -webkit-fill-available; width: -moz-available;">Add</button>`;

                // PERIOD TYPE
                const periodTypeVal = getCellValue(currentRow.children[8]);
                cells[8].innerHTML = `<select name="period_type[]" class="form-select form-select-sm" required>
                                            <option value="">Period Type</option>
                                            <option value="LECTURE" ${periodTypeVal == "LECTURE" ? 'selected' : ''}>Lecture</option>
                                            <option value="PRACTICAL" ${periodTypeVal == "PRACTICAL" ? 'selected' : ''}>Practical</option>
                                            <option value="TUTORIAL" ${periodTypeVal == "TUTORIAL" ? 'selected' : ''}>Tutorial</option>
                                            <option value="DISSECTION" ${periodTypeVal == "DISSECTION" ? 'selected' : ''}>Dissection</option>
                                            <option value="BREAK" ${periodTypeVal == "BREAK" ? 'selected' : ''}>Break</option>
                                            <option value="WARD" ${periodTypeVal == "WARD" ? 'selected' : ''}>Ward</option>
                                            <option value="AUTOPSY" ${periodTypeVal == "AUTOPSY" ? 'selected' : ''}>Autopsy</option>
                                            <option value="CLINICAL METHODS" ${periodTypeVal == "CLINICAL METHODS" ? 'selected' : ''}>Clinical Methods</option>
                                            <option value="CLINICAL DISCUSSION" ${periodTypeVal == "CLINICAL DISCUSSION" ? 'selected' : ''}>Clinical Discussion</option>
                                            <option value="SELF STUDIES" ${periodTypeVal == "SELF STUDIES" ? 'selected' : ''}>Self Studies</option>
                                            <option value="OSPE" ${periodTypeVal == "OSPE" ? 'selected' : ''}>OSPE</option>
                                            <option value="OSCE STATIONS" ${periodTypeVal == "OSCE STATIONS" ? 'selected' : ''}>OSCE Stations</option>
                                            <option value="SHORT CASE" ${periodTypeVal == "SHORT CASE" ? 'selected' : ''}>Short Case</option>
                                            <option value="CLASS DISCUSSION" ${periodTypeVal == "CLASS DISCUSSION" ? 'selected' : ''}>Class Discussion</option>
                                            <option value="CASE PREPARATION" ${periodTypeVal == "CASE PREPARATION" ? 'selected' : ''}>Case Preparation</option>
                                            <option value="CASE PRESENTATION" ${periodTypeVal == "CASE PRESENTATION" ? 'selected' : ''}>Case Presentation</option>
                                    </select>`;
                // HOD
                const hodVal = getCellValue(currentRow.children[9]);
                cells[9].innerHTML = `<input type="text" name="hod[]" class="form-control form-control-sm" required>`;

                // START TIME
                const startTimeVal = getCellValue(currentRow.children[10]);
                cells[10].innerHTML = `<input type="time" name="start_time[]" class="form-control form-control-sm" value="${startTimeVal}" required>`;

                // END TIME
                const endTimeVal = getCellValue(currentRow.children[11]);
                cells[11].innerHTML = `<input type="time" name="end_time[]" class="form-control form-control-sm" value="${endTimeVal}" required>`;

                // Buttons
                const lastCell = clone.lastElementChild;
                lastCell.innerHTML = `
                    <button type="button" class="btn btn-success btn-sm add-more-btn"><i class="fas fa-plus"></i></button>
                    <button type="button" class="btn btn-danger btn-sm delete-row-btn"><i class="fas fa-trash"></i></button>
                `;

                // Insert after current row
                currentRow.parentNode.insertBefore(clone, currentRow.nextSibling);

                // Hide the original row
                originalRow.style.display = 'none';

                // initialize Select2 for subject ID
                $('.js-example-basic-single').select2();

                const hodCell = clone.querySelector('.hod');
                hodCell.addEventListener('click', function (){
                    Swal.fire({
                        title: 'Select HOD',
                        html: `
                            <select id="swal-hod-select" class="form-control" style="width:100%">
                                <option value="">Select HOD</option>
                                @foreach($doctors as $doctor)
                                <option value="{{ $doctor->emp_code }}">{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Select',
                        cancelButtonText: 'Cancel',
                        didOpen: () => {
                            // Initialize Select2 **after** swal is rendered
                            $('#swal-hod-select').select2({
                            dropdownParent: $('.swal2-container'), // ensures dropdown stays inside modal
                            placeholder: "Select HOD",
                            width: '100%'
                            });
                        },
                        preConfirm: () => {
                            const select = $('#swal-hod-select');
                            return {
                                code: select.val(),
                                name: select.find("option:selected").text()
                            };
                        }
                        }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            hodCell.innerHTML = `
                            <select name="hod[]" id="search-hod" class="form-control form-control-sm" required>
                                <option value="${result.value.code}">${result.value.name}</option>
                            </select>
                            `;
                        }
                    });
                });

                // apply sweet alert to topic button
                const addTopicBtn = clone.querySelector('.add-topic-btn');
                addTopicBtn.addEventListener('click', function () {
                    Swal.fire({
                        title: 'Add Topic Name',
                        input: 'text',
                        inputPlaceholder: 'Enter topic name',
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            const topicCell = clone.querySelector('.topic');
                            topicCell.innerHTML = `<input type="text" name="topic[]" class="form-control form-control-sm" value="${result.value}">`;
                        }
                    });
                });
            }

            // DELETE button
            if (e.target.closest('.delete-row-btn')) {
                const row = e.target.closest('tr');
                const originalId = row.dataset.originalId;
                row.remove();

                // If no clones remain, show original
                if (originalId) {
                    const remainingClones = table.querySelectorAll(`tr[data-original-id="${originalId}"]`);
                    if (remainingClones.length === 0) {
                        const originalRow = table.querySelector(`tr[data-row-id="${originalId}"]`);
                        if (originalRow) {
                            originalRow.style.display = '';
                        }
                    }
                }
            }
        });
        // Prevent form submission if no cloned rows exist
        timetableForm.addEventListener('submit', function (e) {
            const clonedRows = table.querySelectorAll('.table-success'); // only check in this table
            if (clonedRows.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: "error",
                    title: "No Rows Added",
                    text: "Please add at least one row before submitting."
                });
                return;
            }

            // --- Time Overlap Validation In A Day ---
            const lecturesByDay = {};

            clonedRows.forEach(row => {
                const day = row.querySelector('[name="day[]"]').value.trim();
                const start = row.querySelector('[name="start_time[]"]').value;
                const end = row.querySelector('[name="end_time[]"]').value;

                if (!day || !start || !end) return;

                if (!lecturesByDay[day]) {
                    lecturesByDay[day] = [];
                }

                lecturesByDay[day].push({ start, end, row });
            });

            // Check overlaps for each day
            for (let day in lecturesByDay) {
                const lectures = lecturesByDay[day];

                for (let i = 0; i < lectures.length; i++) {
                    for (let j = i + 1; j < lectures.length; j++) {
                        const a = lectures[i];
                        const b = lectures[j];

                        // overlap check
                        if (a.start < b.end && b.start < a.end) {
                            e.preventDefault();
                            Swal.fire({
                                icon: "error",
                                title: "Overlap Detected",
                                text: `On ${day}, ${a.start}-${a.end} overlaps with ${b.start}-${b.end}. Please fix it.`
                            });
                            return; // stop checking further
                        }
                    }
                }
            }
        });
    });
    // get the subject title from database when subject_id is selected
    $(document).on('change', '.js-example-basic-single', function (e) {
        if (e.target.matches('.js-example-basic-single')) {
            const selectedValue = $(this).val();
            $.ajax({
                url: '{{ route('timetables.get-subject') }}',
                type: 'POST',
                data: {
                    subject_id: selectedValue,
                    _token: '{{ csrf_token() }}'
                },
                success: function (data) {
                    const subjectTitle = data.main_subject;
                    const row = e.target.closest('tr');
                    row.querySelector('input[name="subject_title[]"]').value = subjectTitle;
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching subject:', error);
                }
            });
        }
    });

@endpush
                