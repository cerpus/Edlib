<x-email-layout>
    <x-slot:title></x-slot:title>

    <p>{{ trans('verify-email.summary', ['site' => config('app.name')]) }}</p>

    <p><a href="{{ $verification_link }}">{{ trans('messages.verify-my-email') }}</a></p>
</x-email-layout>
