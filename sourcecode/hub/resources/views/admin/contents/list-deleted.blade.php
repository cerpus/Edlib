<x-layout>
    <x-slot:title>{{ trans('messages.deleted-contents') }}</x-slot:title>

    <x-admin.back-link />

    {{ $contents->links() }}

    <table class="table table-responsive-lg table-striped">
        <thead>
        <tr>
            <th scope="col" class="w-25">{{ trans('messages.title') }}
            <th scope="col">{{ trans('messages.deleted-at') }}
            <th scope="col">{{ trans('messages.created') }}
            <th scope="col">{{ trans('messages.status') }}
            <th scope="col">{{ trans('messages.content-type') }}
            <th scope="col">{{ trans('messages.language') }}
            <th scope="col">{{ trans('messages.actions') }}
            <th scope="col">
        </tr>
        </thead>

        <tbody>
            @foreach($contents as $content)
                <tr>
                    <td>{{ $content->getCachedLatestVersion()->title }}</td>
                    <td>
                        <time datetime="{{ $content->deleted_at->format('c') }}"></time>
                    </td>
                    <td>
                        <time datetime="{{ $content->created_at->format('c') }}"></time>
                    </td>
                    <td>
                        @if ($content->getCachedLatestVersion()->published)
                            {{ trans('messages.published') }}
                        @else
                            {{ trans('messages.draft') }}
                        @endif
                    </td>
                    <td>{{ $content->getCachedLatestVersion()->displayed_content_type }}</td>
                    <td>{{ $content->getCachedLatestVersion()->getTranslatedLanguage() }}</td>
                    <td>
                        <x-content.preview-link
                            class="btn btn-sm btn-secondary me-2"
                            role="button"
                            :$content
                            :version="$content->getCachedLatestVersion()"
                            :preview-url="route('admin.content.deleted-preview', [$content, $content->getCachedLatestVersion()])"
                            title="{{ trans('messages.preview') }}"
                        >
                            <x-icon name="eye" />
                        </x-content.preview-link>
                        <a
                            href="{{ route('admin.content.restore', [$content]) }}"
                            class="btn btn-sm btn-success me-2"
                            role="button"
                            title="{{ trans('messages.restore') }}"
                        >
                            <x-icon name="reply" />
                        </a>
                        <button
                            class="btn btn-sm btn-danger"
                            hx-delete="{{ route('admin.content.destroy', [$content]) }}"
                            hx-confirm="{{ trans('messages.permanent-delete-confirm') }}"
                            data-confirm-title="{{ trans('messages.permanent-delete') }}"
                            data-confirm-ok="{{ trans('messages.permanent-delete') }}"
                            data-confirm-ok-class="btn-danger"
                            title="{{ trans('messages.permanent-delete') }}"
                        >
                            <x-icon name="x-circle" />
                        </button>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $contents->links() }}
</x-layout>
