<x-layout>
    <x-slot name="title">{{ trans('messages.my-account') }}</x-slot>

    <h6>{{ trans('messages.change-profile-name') }}</h6>
    @if(session('success'))
        <div class="alert alert-success" role="alert" id="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <x-form action="{{ route('user.update-username') }}" method="POST">
        @csrf

        <x-form.field
            name="name"
            type="text"
            :label="trans('messages.name')"
            required
        />

        <x-form.button class="btn-primary">
            {{ trans('messages.save') }}
        </x-form.button>
    </x-form>
</x-layout>
