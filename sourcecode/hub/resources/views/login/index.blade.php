<x-layout>
    <x-slot:title>{{ trans('messages.log-in') }}</x-slot:title>

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
                {{ trans('messages.email-address') }}
                <input type="email" name="email" value="{{ old('email') }}">
            </label>
        </p>

        <p>
            <label>
                {{ trans('messages.password') }}
                <input type="password" name="password">
            </label>
        </p>

        <p><button>{{ trans('messages.log-in') }}</button></p>
    </form>
</x-layout>
