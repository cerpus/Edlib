@isset($lti['content_item_return_url'])
    @php($request = $content->toItemSelectionRequest())
    <form
        action="{{ $request->getUrl() }}"
        method="{{ $request->getMethod() }}"
    >
        {!! $request->toHtmlFormInputs() !!}
        <button class="btn btn-primary me-1 {{ $btnClass ?? '' }}">
            {{ trans('messages.use-content') }}
        </button>
    </form>
@endif
<button class="btn btn-secondary d-none d-md-inline-block me-1 {{ $btnClass ?? '' }}">
    {{ trans('messages.edit-content') }}
</button>
<div class="dropup">
    <button
        type="button"
        class="btn dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="{{ trans('messages.toggle-menu') }}"
    >
        <x-icon name="three-dots-vertical" />
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a href="{{ route('content.preview', [$content->id]) }}" class="dropdown-item">
                <x-icon name="info-lg" class="me-2" />
                {{ trans('messages.preview') }}
            </a>
        </li>
        <li class="d-md-none">
            <a href="{{ route('content.edit', [$content->id]) }}" class="dropdown-item">
                <x-icon name="pencil" class="me-2" />
                {{ trans('messages.edit-content') }}
            </a>
        </li>
        <li>
            <a href="#" class="dropdown-item">
                <x-icon name="x-lg" class="me-2 text-danger" />
                {{ trans('messages.delete-content') }}
            </a>
        </li>
    </ul>
</div>
