@extends('layouts.admin')


@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        <div class="row">
            <h1>Missing some config.</h1>
            <ul>
                <li>IMPORT_USERID in .env is invalid.</li>
            </ul>
        </div>
    </div>
@endsection
