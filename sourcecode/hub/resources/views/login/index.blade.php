@extends('layout')

@section('title', 'Login')

@section('content')
    <form action="{{ route('login_check') }}" method="POST">
        @csrf

        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <p>
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}">
            </label>
        </p>

        <p>
            <label>
                Password
                <input type="password" name="password">
            </label>
        </p>

        <p><button>Log in</button></p>
    </form>
@endsection
