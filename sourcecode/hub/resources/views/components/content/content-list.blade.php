<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3" aria-hidden="true">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $contents->total()) }}
        </div>
        <div class="col-2">
            {{ trans('messages.last-changed') }}
        </div>
        <div class="col-2">
            {{ trans('messages.author') }}
        </div>
        <div class="col-2">
            {{ trans('messages.language') }}
        </div>
        <div class="col-2">
            {{ trans('messages.views') }}
        </div>
    </div>
    <x-content.layout-toggle :title="trans('messages.result-grid')">
        <x-icon name="grid-fill" />
    </x-content.layout-toggle>
</div>
<div wire:loading.delay.remove class="row row-cols-1 g-3 mb-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.list-item
                :$content
                :showDrafts="$showDrafts ?? false"
            />
        </div>
    @endforeach
</div>
