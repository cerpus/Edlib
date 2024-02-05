<div class="position-relative">
    <div class="row flex-row align-items-center mb-2 ps-3 pe-3" aria-hidden="true">
        <div class="col fw-bold mb-1">
            {{ trans_choice('messages.num-content-found', $contents->total()) }}
        </div>
    </div>
    <x-content.layout-toggle :title="trans('messages.result-list')">
        <x-icon name="list" />
    </x-content.layout-toggle>
</div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 row-cols-xl-3 row-cols-xxl-4 g-3 mb-3">
    @foreach ($contents as $content)
        <div class="col">
            <x-content.grid-item
                :$content
                :showDrafts="$showDrafts ?? false"
            />
        </div>
    @endforeach
</div>
