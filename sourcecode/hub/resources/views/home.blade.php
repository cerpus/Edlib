<x-layout no-header>
    <x-slot:title>{{ config('app.name') }}</x-slot:title>

    <h2 class="fs-5 mb-3">{{ trans('messages.recent-content') }}</h2>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 g-3 mb-3">
        @foreach ($contents as $content)
            <div class="col">
                <x-content.card :$content :showDrafts="false" />
            </div>
        @endforeach
    </div>

    <x-preview-modal />
</x-layout>
