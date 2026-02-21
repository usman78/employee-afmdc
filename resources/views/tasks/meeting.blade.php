@extends('layouts.app')
@push('styles')
  .card {
    border-radius: 0;
  }
  .collapse {
    transition: height 0.4s ease;
    visibility: visible;
  }
@endpush
@section('content')
    <section id="services" class="services section">
      <!-- Section Title -->
      <div class="container section-title">
        <h2>Meetings & Tasks</h2>
        <p>View your meetings and tasks to submit assigned task responses from the same screen.</p>
      </div><!-- End Section Title -->
      <div class="container">
        @if ($meetings->isEmpty())
          <div class="alert alert-info text-center" role="alert">
            No meetings recorded yet.
          </div>
        @else  
          <div class="accordion" id="accordionExample">
            @foreach ($meetings as $meeting)
              @php
                $meetingKey = $meeting->meet_no . '|' . $meeting->cat;
                $meetingTasks = $tasksByMeeting->get($meetingKey, collect());
              @endphp
              <div class="accordion-item">
                  <h2 class="accordion-header" id="heading{{ $loop->index }}">
                      <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                              type="button" 
                              data-toggle="collapse" 
                              data-target="#collapse{{ $loop->index }}" 
                              aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                              aria-controls="collapse{{ $loop->index }}">
                          <strong>{{ $meeting->subject }}</strong> &nbsp; - Dated {{ dateFormat($meeting->meet_date) }}
                      </button>
                  </h2>
                  <div id="collapse{{ $loop->index }}" 
                      class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                      aria-labelledby="heading{{ $loop->index }}" 
                      data-parent="#accordionExample">
                      <div class="accordion-body">
                          <b>Meeting Type: </b>{{ $meeting->cat }}<br>
                          <b>Meeting Number: </b>{{ $meeting->meet_no }}<br>
                          <b>Chaired By: </b>{{ capitalizeWords($meeting->chair) ?? 'N/A' }}<br>
                          <b>Venue: </b>{{ $meeting->venue ?? 'N/A' }}<br>
                          <b>Meeting Time: </b>{{ $meeting->time_hh . ':' . $meeting->time_mm }}<br>
                          {!! $meeting->copy_to ? '<b>Copy To: </b>' . $meeting->copy_to : 'No one copied.' !!}<br>
                          {!! $meeting->comments ? '<b>Comments: </b>' . $meeting->comments : 'No comments provided.' !!}<br>
                          <hr>
                          <h5 class="mb-3">Your Assigned Tasks</h5>

                          @if ($meetingTasks->isEmpty())
                            <div class="alert alert-light border mb-0">
                              No tasks assigned to you in this meeting.
                            </div>
                          @else
                            @foreach ($meetingTasks as $task)
                              @php
                                $taskFormId = 'task-' . $meeting->meet_no . '-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $meeting->cat) . '-' . $task->task_no;
                              @endphp
                              <div class="card mb-3">
                                <div class="card-body">
                                  <h6 class="card-title mb-2">{{ $task->task_desc }}</h6>
                                  <b>Task No:</b> {{ $task->task_no }}<br>
                                  <b>Target Date:</b> {{ dateFormat($task->targ_date) }}<br>
                                  <b>Status:</b> {{ ((int) $task->status === 1) ? 'Completed' : 'In Progress / Not Submitted' }}<br>

                                  <div class="mt-2" id="response-section-{{ $taskFormId }}">
                                    @if ($task->prog_desc !== null)
                                      <div class="card mt-2">
                                        <div class="card-body bg-light">
                                          <h6 class="card-subtitle mb-2 text-muted">Task Response</h6>
                                          <p class="card-text">{{ $task->prog_desc }}</p>
                                          <p class="card-subtitle mb-2 text-muted">Task Completion Date</p>
                                          <p class="card-text">{{ $task->compl_date == null ? 'N/A' : dateFormat($task->compl_date) }}</p>
                                          <p class="card-subtitle mb-2 text-muted">Task Report Date</p>
                                          <p class="card-text">{{ $task->rprt_date == null ? 'N/A' : dateFormat($task->rprt_date) }}</p>
                                        </div>
                                      </div>
                                    @else
                                      <button onclick="showResponseForm('{{ $taskFormId }}')" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-plus-circle"></i>
                                        Add Response
                                      </button>
                                    @endif
                                  </div>

                                  <div class="card mt-2 collapse" id="response-form-{{ $taskFormId }}">
                                    <div class="card-body">
                                      <form id="task-form-{{ $taskFormId }}" onsubmit="event.preventDefault(); saveResponse('{{ $taskFormId }}', '{{ route('update-progress') }}')">
                                        @csrf
                                        <div class="mb-2">
                                          <label for="response-text-{{ $taskFormId }}" class="form-label">Task Response</label>
                                          <textarea class="form-control" id="response-text-{{ $taskFormId }}" name="prog_desc" rows="3" required></textarea>
                                        </div>
                                        <div class="mb-2">
                                          <label for="completion-date-{{ $taskFormId }}" class="form-label">Completion Date</label>
                                          <input type="date" class="form-control" id="completion-date-{{ $taskFormId }}" name="compl_date">
                                        </div>
                                        <div class="mb-3">
                                          <label class="form-label">Task Status</label><br>
                                          <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status-{{ $taskFormId }}" id="inprogress-{{ $taskFormId }}" value="0" checked>
                                            <label class="form-check-label" for="inprogress-{{ $taskFormId }}">In Progress</label>
                                          </div>
                                          <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status-{{ $taskFormId }}" id="completed-{{ $taskFormId }}" value="1">
                                            <label class="form-check-label" for="completed-{{ $taskFormId }}">Completed</label>
                                          </div>
                                        </div>

                                        <input type="hidden" name="meet_no" value="{{ $task->meet_no }}">
                                        <input type="hidden" name="cat" value="{{ $task->cat }}">
                                        <input type="hidden" name="task_no" value="{{ $task->task_no }}">
                                        <input type="hidden" name="comp_code" value="{{ $task->comp_code }}">

                                        <button type="submit" class="btn btn-success btn-sm">Save Response</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelResponseForm('{{ $taskFormId }}')">Cancel</button>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          @endif
                      </div>
                  </div>
              </div>
            @endforeach  
          </div>
        @endif  
        <div class="mt-4 flex justify-center">
          {{ $meetings->links('pagination::bootstrap-5') }}
        </div>
      </div>
    </section>
@endsection

@push('scripts')
  function showResponseForm(index) {
    document.getElementById(`response-section-${index}`).classList.add('d-none');

    const formElement = document.getElementById(`response-form-${index}`);
    const bsCollapse = new bootstrap.Collapse(formElement, {
      toggle: true
    });
    bsCollapse.show();
  }

  function cancelResponseForm(index) {
    const formElement = document.getElementById(`response-form-${index}`);
    const collapseInstance = bootstrap.Collapse.getOrCreateInstance(formElement);

    formElement.addEventListener('hidden.bs.collapse', function handler() {
      document.getElementById(`response-section-${index}`).classList.remove('d-none');
      formElement.removeEventListener('hidden.bs.collapse', handler);
    });

    collapseInstance.hide();
  }

  function saveResponse(index, url) {
    const prog_desc = document.getElementById(`response-text-${index}`).value;
    const compl_date = document.getElementById(`completion-date-${index}`).value;
    const status = document.querySelector(`input[name="status-${index}"]:checked`).value;
    const form = document.getElementById(`task-form-${index}`);
    
    const meet_no = form.querySelector('input[name="meet_no"]').value;
    const cat = form.querySelector('input[name="cat"]').value;
    const task_no = form.querySelector('input[name="task_no"]').value;
    const comp_code_input = form.querySelector('input[name="comp_code"]').value.trim();
    const comp_code = comp_code_input === '' ? null : comp_code_input;

    const payload = {
      prog_desc,
      compl_date,
      status,
      meet_no,
      cat,
      task_no
    };

    if (comp_code !== null) {
      payload.comp_code = comp_code;
    }

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(payload)
    })
    .then(response => {
      if (!response.ok) throw new Error('Network response was not ok');
      return response.json();
    })
    .then(() => {
      Swal.fire({
        title: "Response Saved!",
        icon: "success",
      }).then(() => {
        location.reload();
      });
      cancelResponseForm(index);
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        title: "Failed to Update!",
        text: error.message,
        icon: "error",
      });
    });
  }
@endpush
