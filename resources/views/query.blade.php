@extends('layouts.app')
@section('content')
    <form action="{{ route('query.post') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="leave_type">Query</label>
            <input type="text" name="query" class="form-control" id="leave_type" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    @if(isset($test))
        <h2>Query Result:</h2>
        <pre>{{ print_r($test, true) }}</pre>
    @endif
@endsection