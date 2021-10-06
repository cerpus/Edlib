@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-10 mb-3 border-bottom">
                <h1 class="h2">Raw Logs</h1>
                <p class="lead">Default is 2000 lines. Add ?lines=n to read even more lines. </p>
            </div>
            @foreach($log as $line)
                {!! $line !!}<br>
            @endforeach
        </div>
@endsection
