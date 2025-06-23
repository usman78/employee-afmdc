@extends('layouts.app')
@push('styles')
  .card {
    border-radius: 0;
  }
  .collapse {
    transition: height 0.4s ease;
  }
@endpush
@section('content')
  <section id="services" class="services section">
    <!-- Section Title -->
    <div class="container section-title aos-init aos-animate" data-aos="fade-up">
      <h2>Assigned Tasks</h2>
      <p>View your assigned tasks as discussed in meetings to keep track of completion dates.</p>
    </div><!-- End Section Title -->

    <div class="container">
      <!-- Nav tabs -->
      <ul class="nav nav-tabs" id="taskTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="uncompleted-tab" data-bs-toggle="tab" data-bs-target="#uncompleted" type="button" role="tab" aria-controls="uncompleted" aria-selected="true">
            Uncompleted Tasks
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab" aria-controls="completed" aria-selected="false">
            Completed Tasks
          </button>
        </li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content mt-3">
        <!-- Uncompleted Tab -->
        <div class="tab-pane fade show active" id="uncompleted" role="tabpanel" aria-labelledby="uncompleted-tab">
          @if (empty($uncompletedTasks))
            <div class="alert alert-info text-center" role="alert">
              No uncompleted tasks found.
            </div>
          @else
            <div class="accordion" id="accordionUncompleted">
              @foreach ($uncompletedTasks as $task)
                <div class="accordion-item bg-blue-500 text-green p-2">
                  <h2 class="accordion-header" id="headingUncompleted{{ $loop->index }}">
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseUncompleted{{ $loop->index }}"
                            aria-expanded="false"
                            aria-controls="collapseUncompleted{{ $loop->index }}">
                      <strong>{{ Str::words($task->task_desc, 15, '...') }}</strong>
                    </button>
                  </h2>
                  <div id="collapseUncompleted{{ $loop->index }}"
                      class="accordion-collapse collapse"
                      aria-labelledby="headingUncompleted{{ $loop->index }}"
                      data-bs-parent="#accordionUncompleted">
                    <div class="accordion-body">
                      <b>Meeting Type:</b> {{ $task->cat }}<br>
                      <b>Meeting Number:</b> {{ $task->meet_no }}<br>
                      <b>Task Number:</b> {{ $task->task_no }}<br>
                      <b>Target Date:</b> {{ dateFormat($task->targ_date) }}<br>
                      <b>Task Description:</b> {{ $task->task_desc }}<br>
                      <div class="mt-2" id="response-section-{{ $loop->index }}">
                        @if ($task->p_meet_no != null)
                          <div class="card mt-2">
                            <div class="card-body bg-light">
                              <h6 class="card-subtitle mb-2 text-muted">Task Response</h6>
                              <p class="card-text">{{ $task->prog_desc == null ? 'N/A' : $task->prog_desc }}</p>
                              <p class="card-subtitle mb-2 text-muted">Task Completion Date</p>
                              <p class="card-text">{{ $task->compl_date == null ? 'N/A' : dateFormat($task->compl_date) }}</p>
                              <p class="card-subtitle mb-2 text-muted">Task Report Date</p>
                              <p class="card-text">{{ $task->rprt_date == null ? 'N/A' : dateFormat($task->rprt_date) }}</p>
                            </div>
                          </div>
                        @else
                          <button onclick="showResponseForm({{ $loop->index }})" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-plus-circle"></i>
                            Add Response
                          </button>    
                        @endif
                      </div>
                      <!-- Hidden Response Form -->
                      <div class="card mt-2 collapse" id="response-form-{{ $loop->index }}">
                        <div class="card-body">
                          <form id="task-form-{{ $loop->index }}" onsubmit="event.preventDefault(); saveResponse({{ $loop->index }}, '{{ route('update-progress') }}')">
                            @csrf
                            <div class="mb-2">
                              <label for="response-text-{{ $loop->index }}" class="form-label">Task Response</label>
                              <textarea class="form-control" id="response-text-{{ $loop->index }}" name="prog_desc" rows="3" required></textarea>
                            </div>
                            <div class="mb-2">
                              <label for="completion-date-{{ $loop->index }}" class="form-label">Completion Date</label>
                              <input type="date" class="form-control" id="completion-date-{{ $loop->index }}" name="compl_date">
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Task Status</label><br>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status-{{ $loop->index }}" id="inprogress-{{ $loop->index }}" value="0" checked>
                                <label class="form-check-label" for="inprogress-{{ $loop->index }}">In Progress</label>
                              </div>
                              <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status-{{ $loop->index }}" id="completed-{{ $loop->index }}" value="1">
                                <label class="form-check-label" for="completed-{{ $loop->index }}">Completed</label>
                              </div>
                            </div>

                            <input type="hidden" name="meet_no" value="{{ $task->meet_no }}">
                            <input type="hidden" name="cat" value="{{ $task->cat }}">
                            <input type="hidden" name="task_no" value="{{ $task->task_no }}">
                            <input type="hidden" name="comp_code" value="{{ $task->comp_code }}">

                            <button type="submit" class="btn btn-success btn-sm">Save Response</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelResponseForm({{ $loop->index }})">Cancel</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="mt-4 flex justify-center">
              {{ $uncompletedTasks->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>

        <!-- Completed Tab -->
        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
          @if (empty($completedTasks))
            <div class="alert alert-info text-center" role="alert">
              No completed tasks found.
            </div>
          @else
            <div class="accordion" id="accordionCompleted">
              @foreach ($completedTasks as $task)
                <div class="accordion-item bg-light p-2">
                  <h2 class="accordion-header" id="headingCompleted{{ $loop->index }}">
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCompleted{{ $loop->index }}"
                            aria-expanded="false"
                            aria-controls="collapseCompleted{{ $loop->index }}">
                      <strong>{{ Str::words($task->task_desc, 15, '...') }}</strong>
                    </button>
                  </h2>
                  <div id="collapseCompleted{{ $loop->index }}"
                      class="accordion-collapse collapse"
                      aria-labelledby="headingCompleted{{ $loop->index }}"
                      data-bs-parent="#accordionCompleted">
                    <div class="accordion-body">
                      <b>Meeting Type:</b> {{ $task->t_cat }}<br>
                      <b>Meeting Number:</b> {{ $task->t_meet_no }}<br>
                      <b>Task Number:</b> {{ $task->t_task_no }}<br>
                      <b>Target Date:</b> {{ dateFormat($task->targ_date) }}<br>
                      <b>Task Description:</b> {{ $task->task_desc }}<br>
                      {{-- @if ($taskProgress) --}}
                        <div class="card mt-2">
                          <div class="card-body bg-light">
                            <h6 class="card-subtitle mb-2 text-muted">Task Response</h6>
                            <p class="card-text">{{ $task->prog_desc == null ? 'N/A' : $task->prog_desc }}</p>
                            <p class="card-subtitle mb-2 text-muted">Task Completion Date</p>
                            <p class="card-text">{{ $task->compl_date == null ? 'N/A' : dateFormat($task->compl_date) }}</p>
                            <p class="card-subtitle mb-2 text-muted">Task Report Date</p>
                            <p class="card-text">{{ $task->rprt_date == null ? 'N/A' : dateFormat($task->rprt_date) }}</p>
                          </div>
                        </div>
                      {{-- @else --}}
                        {{-- <div class="text-muted fst-italic mt-2">
                          No response recorded for this task.
                        </div> --}}
                      {{-- @endif --}}

                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            <div class="mt-4 flex justify-center">
              {{ $completedTasks->links('pagination::bootstrap-5') }}
            </div>
          @endif
        </div>
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
    bsCollapse.show(); // <-- explicitly show
  }

  function cancelResponseForm(index) {
    const formElement = document.getElementById(`response-form-${index}`);
    const collapseInstance = bootstrap.Collapse.getOrCreateInstance(formElement);

    formElement.addEventListener('hidden.bs.collapse', function handler() {
      document.getElementById(`response-section-${index}`).classList.remove('d-none');
      formElement.removeEventListener('hidden.bs.collapse', handler);
    });

    collapseInstance.hide(); // <-- triggers smooth slide up
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

    // Base payload
    const payload = {
      prog_desc,
      compl_date,
      status,
      meet_no,
      cat,
      task_no
    };

    // Add comp_code only if it's not null
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
    .then(data => {
      Swal.fire({
        title: "Response Saved!",
        icon: "success",
      }).then(() => {
        location.reload(); // Reloads the page to reflect changes
      });
      // Optionally reload or update UI
      cancelResponseForm(index); // collapse the form
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