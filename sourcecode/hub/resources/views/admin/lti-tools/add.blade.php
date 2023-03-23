@php use App\Models\LtiVersion; @endphp

@extends('layout')

@section('title', 'Add LTI tool')

@section('content')
    <form action="{{ route('admin.lti-tools.store') }}" method="POST">
        @csrf

        @if ($errors->any())
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        @endif

        <p>
            <label>
                Name
                <input type="text" name="name" required>
            </label>
        </p>

        <p>
            <label>
                LTI version
                <select name="lti_version">
                    <option value="" selected>Pick one…</option>
                    <option value="{{ LtiVersion::Lti1_1->value }}">1.1</option>
                    <option value="{{ LtiVersion::Lti1_3->value }}">1.3</option>
                </select>
            </label>
        </p>

        <p>
            <label>
                Creator launch URL
                <input type="text" inputmode="url" name="creator_launch_url" required>
            </label>
        </p>

        <p>
            <label>
                Consumer key
                <input type="text" name="consumer_key">
            </label>
        </p>

        <p>
            <label>
                Consumer secret
                <input type="password" name="consumer_secret">
            </label>
        </p>

        <p>
            <button>Add</button>
        </p>
    </form>
@endsection
