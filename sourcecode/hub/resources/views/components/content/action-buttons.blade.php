@props(['content'])

@if ($content->useUrl)
    <x-content.action-buttons.use :url="$content->useUrl" />
    @if($content->editUrl)
        <x-content.action-buttons.edit :url="$content->editUrl" />
    @else
        <x-content.action-buttons.copy :url="$content->copyUrl" />
    @endif
    <x-content.action-buttons.menu
        :shareUrl="$content->shareUrl"
        :shareDialogUrl="$content->shareDialogUrl"
        :detailsUrl="$content->detailsUrl"
        :copyUrl="$content->editUrl ? $content->copyUrl : null"
        :deleteUrl="$content->deleteUrl"
    />
@elseauth
    <x-content.action-buttons.details :url="$content->detailsUrl" />
    @if($content->editUrl)
        <x-content.action-buttons.edit :url="$content->editUrl" />
    @else
        <x-content.action-buttons.copy :url="$content->copyUrl" />
    @endif
    <x-content.action-buttons.menu
        :shareUrl="$content->shareUrl"
        :shareDialogUrl="$content->shareDialogUrl"
        :copyUrl="$content->editUrl ? $content->copyUrl : null"
        :deleteUrl="$content->deleteUrl"
    />
@else
    <x-content.action-buttons.share :$content />
    <x-content.action-buttons.details :url="$content->detailsUrl" />
@endif
