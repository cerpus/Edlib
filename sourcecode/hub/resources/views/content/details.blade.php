@props(['version', 'explicitVersion' => false])
<x-layout no-header>
    <x-slot:title>{{ $version->title }}</x-slot:title>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    @if (!$version->published)
        <p class="alert alert-warning" role="alert">
            {{ trans('messages.viewing-draft-version-notice') }}
            @if ($explicitVersion && $content->latestPublishedVersion()->exists())
                <a href="{{ route('content.details', [$content]) }}">{{ trans('messages.view-latest-published-version') }}</a>
            @endif
        </p>
    @elseif ($explicitVersion && !$content->latestPublishedVersion()->is($version))
        <p class="alert alert-info">
            {{ trans('messages.viewing-old-version-notice') }}
            @if ($content->latestPublishedVersion()->exists())
                <a href="{{ route('content.details', $content) }}">{{ trans('messages.view-latest-version') }}</a>
            @endif
        </p>
    @endif

    <x-content.details.header :$version current="content" />

    <x-layout.sidebar-layout>
        <x-slot:main>
            <x-lti-launch :launch="$launch" log-to="#messages" class="w-100 border mb-2" />
        </x-slot:main>

        <x-slot:sidebar>
            <div class="d-flex d-lg-none flex-wrap justify-content-end gap-2">
                <x-content.details.action-buttons :$content :$version :$explicitVersion />
            </div>

            @can('edit', [$content])
                @if ($content->latestVersion?->is($version))
                    @if (!$version->published)
                        <x-form action="{{ route('content.publish-version', [$content, $version]) }}" method="PATCH">
                            <x-form.button class="btn-primary w-100 mb-3">
                                {{ trans('messages.publish') }}
                            </x-form.button>
                        </x-form>
                    @else
                        {{-- TODO: unpublish toggle --}}
                    @endif
                @endif

                <x-form class="form-check form-switch mb-3">
                    <x-form.checkbox
                        name="shared"
                        :checked="$content->shared"
                        role="switch"
                        id="shared-toggle"
                        hx-patch="{{ route('content.update-status', [$content]) }}"
                    />
                    <label class="form-check-label" for="shared-toggle">{{ trans('messages.share-the-content') }}</label>
                </x-form>
            @endcan

            <table class="table table-sm table-striped caption-top">
                <caption class="pt-0">{{ trans('messages.details') }}</caption>
                <tbody>
                    <tr>
                        <th scope="row">{{ trans('messages.views') }}</th>
                        <td class="content-details-total-views">{{ $content->countTotalViews() }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ trans('messages.created-with') }}</th>
                        <td>
                            {{ $version->tool->name }}
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ trans('messages.content-type') }}</th>
                        <td>
                            {{ $version->displayed_content_type }}
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ trans('messages.created') }}</th>
                        <td>
                            <time
                                datetime="{{ $content->created_at->toIso8601String() }}"
                                data-dh-relative="true"
                            ></time>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-sm table-striped caption-top">
                <caption>{{ trans('messages.this-version') }}</caption>
                <tbody>
                    <tr>
                        <th scope="row">{{ trans('messages.edited') }}</th>
                        <td>
                            <time
                                datetime="{{$version->created_at->toIso8601String()}}"
                                data-dh-relative="true"
                            ></time>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{ trans('messages.edited-by') }}</th>
                        <td>
                            {{$version->editedBy?->name}}
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">{{trans('messages.license')}}</th>
                        <td>{{$version->license}}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{trans('messages.language')}}</th>
                        <td>{{$version->getTranslatedLanguage()}}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ trans('messages.status') }}</th>
                        <td>
                            @if ($version->published)
                                {{ trans('messages.published') }}
                            @else
                                {{ trans('messages.draft') }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
                <x-content.details.lti-params :$launch :$version />
                <x-content.details.messages id="messages" />
            @endif
        </x-slot:sidebar>
    </x-layout.sidebar-layout>
</x-layout>
