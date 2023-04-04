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

        <x-form.field name="email" :label="trans('messages.email-address')"/>
        <x-form.field name="password" :label="trans('messages.password')" type="password" />

        <x-form.button class="btn-primary">{{ trans('messages.log-in') }}</x-form.button>
    </form>
</x-layout>
