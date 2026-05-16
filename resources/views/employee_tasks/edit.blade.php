@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Task</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="{{ route('employee-tasks.update', $task) }}">
                @method('PUT')
                @include('employee_tasks._form')
            </form>
        </div>
    </div>
</div>
@endsection
