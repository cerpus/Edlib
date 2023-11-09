<x-sidebar-layout>
    <x-slot:title>{{ $content->latestPublishedVersion->resource->title }}</x-slot:title>

    <div class="container mt-1">
        <div class="row">
            <div class="col-6">
                {{ trans('messages.created')}}: {{ $content->created_at->format('M d, Y') }} by {{ $authorName }}
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-12">
                {{ trans('messages.last-updated')}}: {{ $content->updated_at->format('M d, Y') }}
            </div>
        </div>
    </div>

    <x-slot:head>
        <x-oembed-links />
    </x-slot:head>

    <div class="container">
        <div class="row justify-content-center">
            <div class="row justify-content-start">
                <x-lti-launch :launch="$launch" />
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="buttons-container d-flex justify-content-between w-100">
                <div>
                    <button type="button" class="btn btn-secondary" aria-label="{{ trans('messages.edit')}}">
                        {{ trans('messages.edit')}}
                        <x-icon name="pencil" class="me-1" />
                    </button>
                    <button type="button" class="btn btn-secondary" aria-label="{{ trans('messages.use-resource')}}">
                        {{ trans('messages.use-resource')}}
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary" aria-label="{{ trans('messages.delete')}}" data-bs-toggle="modal" data-bs-target="#deletionModal">
                        {{ trans('messages.delete')}}
                        <x-icon name="trash" class="me-1" />
                    </button>
                </div>
            </div>
        </div>
        <hr/>

        <div class="row justify-content-around mt-4">
            <ul class="nav nav-underline d-flex w-100 justify-content-between">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">{{ trans('messages.licensing')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">{{ trans('messages.user-statistics')}} <x-icon name="graph-up-arrow" class="text-black"/></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">{{ trans('messages.using-resource-on-lms')}}</a>
                </li>
            </ul>
        </div>
    </div>

    <x-slot:sidebar>
        @can('edit', $content)
            <section>
                <h2 class="fs-5">{{ trans('messages.version-history') }}
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('messages.version-history-tooltip')}}">
                        <x-icon name="info-circle" class="ms-2" />
                    </span>
                </h2>
                <ul class="p-0">
                    @foreach ($content->versions->sortByDesc('created_at')->take(3) as $index => $version)
                        <li class="d-flex flex-column p-1 mb-1 rounded {{ $loop->first ? 'border border-success bg-success-subtle' : 'border border-secondary bg-white' }}">
                            <div class="version-details-container d-flex w-100 align-items-center justify-content-between">
                                <div class="version-details-80">
                                    <div class="version-number">
                                        <b>{{ trans('messages.version') }} {{ $index + 1 }}</b>
                                    </div>
                                    <div class="version-date">
                                        {{ $version->created_at }}
                                    </div>
                                    <div class="version-status">
                                        @if ($version->published)
                                            {{ trans('messages.published') }}
                                        @else
                                            <span>{{ trans('messages.unpublished') }}</span>
                                        @endif
                                    </div>
                                </div>
                                @unless ($version->published)
                                    <div class="rounded-pill bg-light d-flex justify-content-center align-items-center p-2 m-2 text-black fw-bold bg-light">Draft</div>
                                @else
                                    <div class="version-icons-20 d-flex justify-content-center align-items-center p-2 m-2">
                                        <span class="text-black fw-bold"><x-icon name="check2-circle" class="text-black"/></span>
                                    </div>
                                @endunless
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if(count($content->versions) > 3)
                    <button class="btn btn-link d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVersions" aria-expanded="false">
                        <x-icon name="chevron-down" class="text-black"/>
                    </button>
                    <div class="collapse" id="collapseVersions">
                        <ul class="p-0">
                            @foreach ($content->versions->sortByDesc('created_at')->slice(3) as $index => $version)
                                <li class="d-flex flex-column p-1 mb-1 rounded {{ $loop->first ? 'border border-success bg-success-subtle' : 'border border-secondary bg-white' }}">
                                    <div class="version-details-container d-flex w-100 align-items-center justify-content-between">
                                        <div class="version-details-80">
                                            <div class="version-number">
                                                <b>{{ trans('messages.version') }} {{ $index + 1 }}</b>
                                            </div>
                                            <div class="version-date">
                                                {{ $version->created_at }}
                                            </div>
                                            <div class="version-status">
                                                @if ($version->published)
                                                    {{ trans('messages.published') }}
                                                @else
                                                    <span>{{ trans('messages.unpublished') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @unless ($version->published)
                                            <div class="rounded-pill bg-light d-flex justify-content-center align-items-center p-2 m-2 text-black fw-bold bg-light">Draft</div>
                                        @else
                                            <div class="version-icons-20 d-flex justify-content-center align-items-center p-2 m-2">
                                                <span class="text-black fw-bold"><x-icon name="check2-circle" class="text-black"/></span>
                                            </div>
                                        @endunless
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>
        @endcan

        @if (auth()->user()?->debug_mode ?? app()->hasDebugModeEnabled())
            @php($version = $content->latestVersion)

            <details>
                <summary>Debug</summary>

                <dl>
                    <dt>ID</dt>
                    <dd><kbd>{{ $content->id }}</kbd></dd>

                    <dt>Version ID</Dt>
                    <dd><kbd>{{ $version->id }}</kbd></dd>

                    <dt>Resource ID</dt>
                    <dd><kbd>{{ $version->lti_resource_id }}</kbd></dd>

                    <dt>Presentation launch URL</dt>
                    <dd><kbd>{{ $version->resource->view_launch_url }}</kbd></dd>
                </dl>

                <x-lti-debug :request="$launch->getRequest()" />
            </details>
        @endif
    </x-slot:sidebar>
</x-sidebar-layout>
<x-delete-modal />
