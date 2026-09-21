@extends('layouts.app')

@section('content')
    // make a table layout for the json response
    <table class="table table-bordered">
        <thead>
            <tr>
                @if(isset($jsonResponse) && count(json_decode($jsonResponse, true)) > 0)
                    @foreach(array_keys((array)json_decode($jsonResponse, true)[0]) as $key)
                        <th>{{ $key }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @if(isset($jsonResponse))
                @foreach(json_decode($jsonResponse, true) as $row)
                    <tr>
                        @foreach($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
@endsection