@props(['content', 'oob' => false])

<div
    class="action-buttons-container"
    @if($content->id)
        id="action-buttons-{{ $content->id }}"
    @endif
    @if($oob && $content->id)
        hx-swap-oob="outerHTML:#action-buttons-{{ $content->id }}"
    @endif
    @if($content->editUrl && $content->actionButtonsUrl && ($pollingInterval = \App\Configuration\Features::listPollingInterval()))
        hx-get="{{ $content->actionButtonsUrl }}"
        hx-trigger="every {{ $pollingInterval }}"
        hx-swap="outerHTML"
    @endif
>
@if ($content->useUrl)
    <x-content.action-buttons.use :url="$content->useUrl" />
    @if($content->editUrl)
        <x-content.action-buttons.edit
            :url="$content->editUrl"
            :lockedByUserName="$content->lockedByUserName"
        />
    @else
        <x-content.action-buttons.copy :url="$content->copyUrl" />
    @endif
    <x-content.action-buttons.menu
        :shareUrl="$content->shareUrl"
        :shareDialogUrl="$content->shareDialogUrl"
        :detailsUrl="$content->detailsUrl"
        :copyUrl="$content->editUrl ? $content->copyUrl : null"
        :deleteUrl="$content->deleteUrl"
        :lockedByUserName="$content->lockedByUserName"
    />
@elseauth
    <x-content.action-buttons.details :url="$content->detailsUrl" />
    @if($content->editUrl)
        <x-content.action-buttons.edit
            :url="$content->editUrl"
            :lockedByUserName="$content->lockedByUserName"
        />
    @else
        <x-content.action-buttons.copy :url="$content->copyUrl" />
    @endif
    <x-content.action-buttons.menu
        :shareUrl="$content->shareUrl"
        :shareDialogUrl="$content->shareDialogUrl"
        :copyUrl="$content->editUrl ? $content->copyUrl : null"
        :deleteUrl="$content->deleteUrl"
        :lockedByUserName="$content->lockedByUserName"
    />
@else
    <x-content.action-buttons.share :$content />
    <x-content.action-buttons.details :url="$content->detailsUrl" />
@endif
</div>
