@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row mt-5">
            <div class="col-md-12 d-block mx-auto">  
                <div class="portfolio-details">
                    <div class="portfolio-info">
                        <h3>Time Table</h3>
                        <p>Here you can view the time table for the current month.</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="calendar"></div>
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
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: '/calendar/events',
            height: 'auto',
        });

        calendar.render();
    });   
@endpush                