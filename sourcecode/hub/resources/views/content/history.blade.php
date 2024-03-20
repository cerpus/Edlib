<x-layout no-header>
    <x-slot:title>Version history</x-slot:title>

    <x-content.details.header :version="$content->latestVersion" current="history" />

    <table class="table table-responsive-lg">
        <thead>
            <tr>
                <th scope="col">{{ trans('messages.title') }}
                <th scope="col">{{ trans('messages.last-updated') }}
                <th scope="col">{{ trans('messages.edited-by') }}
                <th scope="col">{{ trans('messages.status') }}
                <th scope="col">{{ trans('messages.language') }}
                <th scope="col">{{ trans('messages.license') }}
                <th scope="col">
            </tr>
        </thead>

        <tbody>
            @foreach ($versions as $version)
                <tr>
                    <td>{{ $version->title }}
                    <td>
                        <a href="{{ route('content.version-details', [$content, $version]) }}">
                            <time datetime="{{ $version->created_at->format('c') }}"></time>
                        </a>
                    </td>
                    <td>{{ $version->editedBy?->name }}</td>
                    <td>
                        @if ($version->published)
                            {{ trans('messages.published') }}
                        @else
                            {{ trans('messages.draft') }}
                        @endif
                    </td>
                    <td>{{ $version->language_iso_639_3 }}
                    <td>{{ $version->license }}
                    <td><x-content.preview-link :$version>{{ trans('messages.preview') }}</x-content.preview-link>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $versions->links() }}
</x-layout>
