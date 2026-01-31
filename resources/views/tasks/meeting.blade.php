@extends('layouts.app')
@section('content')
    <section id="services" class="services section">
      <!-- Section Title -->
      <div class="container section-title">
        <h2>Meetings</h2>
        <p>View your minutes of meetings.</p>
      </div><!-- End Section Title -->
      <div class="container">
        @if (empty($meetings))
          <div class="alert alert-info text-center" role="alert">
            No meetings recorded yet.
          </div>
        @else  
          <div class="accordion" id="accordionExample">
            @foreach ($meetings as $meeting)
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