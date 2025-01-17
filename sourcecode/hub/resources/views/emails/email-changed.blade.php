<x-email-layout>
    <x-slot:title>{{ trans('email-changed.subject') }}</x-slot:title>

    <p>{{ trans('email-changed.summary', ['site' => config('app.name')]) }}</p>

    <p>{{ trans('email-changed.unrecognised-action', ['site' => config('app.name')]) }}</p>
</x-email-layout>
