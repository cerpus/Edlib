@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        @if($content->library)
            <a href="{{ route('admin.content-library', $content->library->id) }}">Library content list</a>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            Details for content "<strong>{{ $content->title }}</strong>"
                        </h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <tr>
                                <th>Id</th>
                                <td>
                                    {{ $content->id }}
                                    <form
                                        action="{{route('admin.content-preview', $content)}}"
                                        method="POST"
                                        target="_blank"
                                        style="display:inline-block; margin-left:1em;"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            title="Preview"
                                            aria-label="Preview"
                                        >
                                            <i aria-hidden="true" class="glyphicon glyphicon-eye-open"></i>
                                        </button>
                                    </form>
                                    <form
                                        action="{{route('admin.content-export', $content)}}"
                                        method="POST"
                                        target="_blank"
                                        style="display:inline-block; margin-left:1em;"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            title="Export"
                                            aria-label="Export"
                                        >
                                            <i aria-hidden="true" class="glyphicon glyphicon-export"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <th>Resource/Folium id</th>
                                <td>{{ $resource?->id ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td>{{ $content->title }}</td>
                            </tr>
                            <tr>
                                <th>Created (Content author)</th>
                                <td>{{ $content->created_at->format('Y-m-d H:i:s e') }}</td>
                            </tr>
                            <tr>
                                <th>Created (Resource API)</th>
                                <td>{{
                                    $resource ?
                                        $resource->createdAt ? \Carbon\Carbon::make($resource->createdAt)->utc()->format('Y-m-d H:i:s e') : '-'
                                    : ''
                                }}</td>
                            </tr>
                            <tr>
                                <th>Updated (Content author)</th>
                                <td>{{ $content->updated_at->format('Y-m-d H:i:s e') }}</td>
                            </tr>
                            <tr>
                                <th>Updated (Resource API)</th>
                                <td>{{
                                    $resource ?
                                        $resource->updatedAt ? \Carbon\Carbon::make($resource->updatedAt)->utc()->format('Y-m-d H:i:s e') : '-'
                                    : ''
                                }}</td>
                            </tr>
                            <tr>
                                <th>Deleted (Resource API)</th>
                                <td>{{
                                    $resource ?
                                        $resource->deletedAt ? \Carbon\Carbon::make($resource->deletedAt)->utc()->format('Y-m-d H:i:s e') : 'No'
                                    : ''
                                }}</td>
                            </tr>
                            <tr>
                                <th>Latest version</th>
                                <td>{{ $latestVersion ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Edlib language</th>
                                <td>
                                    @isset($content->language_iso_639_3)
                                        {{ $content->language_iso_639_3 }} ({{ Iso639p3::englishName($content->language_iso_639_3) }})
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <th>H5P language</th>
                                <td>
                                    @isset($content->metadata->default_language)
                                        {{ $content->metadata->default_language }} ({{{Iso639p3::englishName($content->metadata->default_language)}}})
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <th>License</th>
                                <td>{{ $content->license }}</td>
                            </tr>
                            <tr>
                                <th>Published</th>
                                <td>{{ $content->isPublished() ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Listed</th>
                                <td>{{ $content->isListed() ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Has lock</th>
                                <td>
                                    {{ $hasLock ?
                                        sprintf('Yes. Last updated %s, expires %s', $hasLock->format('H:i:s e'), $hasLock->addSeconds(\App\ContentLock::EXPIRES)->format('H:i:s e'))
                                        : 'No'
                                    }}
                                </td>
                            </tr>
                            <tr>
                                <th>Library</th>
                                <td>
                                    @if($content->library)
                                        <a href="{{ route('admin.check-library', [$content->library->id]) }}">
                                            {{ $content->library->getLibraryString(true) }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @empty($history)
                    <div class="alert alert-warning">
                        No history found
                    </div>
                @else
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>History</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Date (Version)</th>
                                    <th>Title</th>
                                    <th>License</th>
                                    <th>Language</th>
                                    <th>Reason</th>
                                    <th>Library</th>
                                </tr>
                                </thead>
                                <tbody>
                                @while(true)
                                    @php
                                        if (!isset($itemId)) {
                                            $itemId = $content->id;
                                        } else {
                                            $itemId = !empty($history[$itemId]['parent']) && !empty($history[$history[$itemId]['parent']]) ? $history[$itemId]['parent'] : null;
                                        }
                                        if ($itemId === null) {
                                            break;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            @if ($itemId !== $content->id && isset($history[$itemId]['content']))
                                                <a href="{{ route('admin.content-details', [$history[$itemId]['content_id']]) }}">
                                                    {{ $history[$itemId]['content_id'] }}
                                                </a>
                                            @else
                                                {{ $history[$itemId]['content_id'] }}
                                            @endif
                                        </td>
                                        <td>{{ $history[$itemId]['versionDate']->format('Y-m-d H:i:s.u e') }}</td>
                                        <td>{{ $history[$itemId]['content']['title'] ?? '' }}</td>
                                        <td>{{ $history[$itemId]['content']['license'] ?? '' }}</td>
                                        <td>{{ $history[$itemId]['content']['language'] ?? '' }}</td>
                                        <td>{{ $history[$itemId]['version_purpose'] }}</td>
                                        <td>
                                            @if(isset($history[$itemId]['content']) && isset($history[$itemId]['content']['library_id']))
                                                <a href="{{ route('admin.check-library', [$history[$itemId]['content']['library_id']]) }}">
                                                    {{ $history[$itemId]['content']['library'] }}
                                                </a>
                                            @else
                                                {{ $history[$itemId]['content']['library'] ?? '' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endwhile
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>Content from this version</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Date (Version)</th>
                                    <th>Title</th>
                                    <th>License</th>
                                    <th>Language</th>
                                    <th>Reason</th>
                                    <th>Library</th>
                                </tr>
                                </thead>
                                <tbody>
                                @empty($history[$content->id]['children'])
                                    <tr>
                                        <td colspan="6">
                                            {{ $latestVersion ? 'This is the latest version' : 'No content found' }}
                                        </td>
                                    </tr>
                                @else
                                    @foreach($history[$content->id]['children'] as $itemId)
                                        <tr>
                                            <td>
                                                @isset($history[$itemId]['content'])
                                                    <a href="{{ route('admin.content-details', [$history[$itemId]['content_id']]) }}">{{ $history[$itemId]['content_id'] }}</a>
                                                @else
                                                    {{ $history[$itemId]['external_reference'] }}
                                                @endisset
                                            </td>
                                            <td>{{ $history[$itemId]['versionDate']->format('Y-m-d H:i:s.u e') }}</td>
                                            <td>{{ $history[$itemId]['content']['title'] ?? '' }}</td>
                                            <td>{{ $history[$itemId]['content']['license'] ?? '' }}</td>
                                            <td>{{ $history[$itemId]['content']['language'] ?? '' }}</td>
                                            <td>{{ $history[$itemId]['version_purpose'] }}</td>
                                            <td>
                                                @if(isset($history[$itemId]['content']) && isset($history[$itemId]['content']['library_id']))
                                                    <a href="{{ route('admin.check-library', [$history[$itemId]['content']['library_id']]) }}">
                                                        {{ $history[$itemId]['content']['library'] }}
                                                    </a>
                                                @else
                                                    {{ $history[$itemId]['content']['library'] ?? '' }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endempty
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endempty
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Libraries</h4>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Library</th>
                                <th>Library type</th>
                                <th>Dependency type</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($content->contentLibraries()->orderBy('dependency_type')->orderBy('weight')->get() as $contentLib)
                                <tr @class(['alert-danger' => !$contentLib->library])>
                                    <td>
                                        @if($contentLib->library)
                                            <a href="{{ route('admin.check-library', [$contentLib->library_id]) }}">
                                                {{ $contentLib->library->getLibraryString(true) }}
                                            </a>
                                        @else
                                            - Library with id {{$contentLib->library_id}} was not found -
                                        @endif
                                    </td>
                                    <td>
                                        @if($contentLib->library)
                                            @if($contentLib->library->runnable)
                                                Content type
                                            @else
                                                Library
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        {{ $contentLib->dependency_type }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
