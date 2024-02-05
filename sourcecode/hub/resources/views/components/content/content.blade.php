@php($layout = \Illuminate\Support\Facades\Session::get('contentLayout', 'grid'))

<div wire:loading.delay class="spinner-border text-info" role="status">
    <span class="visually-hidden">{{ trans('messages.loading') }}</span>
</div>
<div wire:loading.delay.remove>
    @if($layout === 'grid')
        <x-content.content-grid :$contents :$showDrafts />
    @else
        <x-content.content-list :$contents :$showDrafts />
    @endif
</div>

{{ $contents->links() }}

<x-preview-modal />
<x-delete-modal />
