<x-email-layout>
    <x-slot:title>{{ trans('messages.reset-password') }}</x-slot:title>
    <p>{{ trans('messages.reset-password-email-summary') }}</p>
    <p>{{ trans('messages.reset-password-email-action') }} <a href="{{ $resetLink }}">{{ trans('messages.reset-password') }}</a></p>
    <p>{{ trans('messages.reset-password-email-unknown') }}</p>
    <p>{{ trans('messages.reset-password-email-thanks', ['site' => config('app.name')]) }}</p>
</x-email-layout>
