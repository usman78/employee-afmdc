@extends('layouts.app')
@section('content')
    <section id="services" class="services section">
      <!-- Section Title -->
      <div class="container section-title aos-init aos-animate" data-aos="fade-up">
        <h2>Assigned Tasks</h2>
        <p>View your assigned tasks as discussed in meetings to keep track of completion dates.</p>
      </div><!-- End Section Title -->
      <div class="container">
        @if ($assignedTasks->isEmpty())
          <div class="alert alert-info text-center" role="alert">
            No tasks assigned to you yet.
          </div>
        @else
          <div class="accordion" id="accordionExample">
            @foreach ($assignedTasks as $task)
              <div class="accordion-item bg-blue-500 text-green p-2">
                  <h2 class="accordion-header" id="heading{{ $loop->index }}">
                      <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                              type="button" 
                              data-bs-toggle="collapse" 
                              data-bs-target="#collapse{{ $loop->index }}" 
                              aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                              aria-controls="collapse{{ $loop->index }}">
                          <strong>{{ Str::words($task->task_desc, 15, '...') }}</strong>
                      </button>
                  </h2>
                  <div id="collapse{{ $loop->index }}" 
                      class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                      aria-labelledby="heading{{ $loop->index }}" 
                      data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                          <b>Meeting Type: </b>{{ $task->cat }}<br>
                          <b>Meeting Number: </b>{{ $task->meet_no }}<br>
                          <b>Task Number: </b>{{ $task->task_no }}<br>
                          <b>Target Date: </b>{{ dateFormat($task->targ_date) }}<br>
                          <b>Task Description: </b>{{ $task->task_desc }}<br>     
                      </div>
                  </div>
              </div>
            @endforeach  
        </div>
        <div class="mt-4 flex justify-center">
          {{ $assignedTasks->links('pagination::bootstrap-5') }}
        </div>  
        @endif
      </div>
    </section>
@endsection     