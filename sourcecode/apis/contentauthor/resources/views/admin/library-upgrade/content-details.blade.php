@extends ('layouts.admin')
@section ('content')
    <div class="container-admin">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        @isset($content->library)
            <a href="{{ route('admin.content-library', $content->library->id) }}">Library content list</a>
        @endisset
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
                                            class="btn btn-default"
                                        >
                                            <span aria-hidden="true" class="fa fa-television"></span>
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
                                            class="btn btn-default"
                                        >
                                            <i aria-hidden="true" class="fa fa-download"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td>{{ $content->title }}</td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td>{{ $content->created_at->format('Y-m-d H:i:s e') }}</td>
                            </tr>
                            <tr>
                                <th>Updated</th>
                                <td>{{ $content->updated_at->format('Y-m-d H:i:s e') }}</td>
                            </tr>
                            <tr>
                                <th>Is leaf</th>
                                <td>
                                    @if($requestedVersion)
                                        {{ $requestedVersion?->isLeaf() ? 'Yes' : 'No' }}
                                    @elseif($content->getVersion())
                                        {{ $content->getVersion()->isLeaf() ? 'Yes' : 'No' }}
                                    @endempty
                                </td>
                            </tr>
                            <tr>
                                <th>Latest version id</th>
                                <td>
                                    @if($requestedVersion && $requestedVersion->id !== $content->version_id)
                                        <a href="{{ route('admin.content-details', [$content->id]) }}">
                                            {{ $content->version_id }}
                                        </a>
                                    @else
                                        {{ $content->version_id }}
                                    @endif
                                </td>
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
                                <td>{{ $content->license ?? ''}}</td>
                            </tr>
                            <tr>
                                <th>Library</th>
                                <td>
                                    @isset($content->library)
                                        <a href="{{ route('admin.check-library', [$content->library->id]) }}">
                                            {{ $content->library->getLibraryString(true) }}
                                        </a>
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Metadata</h4>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th>Authors</th>
                                    <td>
                                        @php
                                            $authors = $content?->metadata?->authors ? json_decode($content->metadata->authors) : []
                                        @endphp
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Role</th>
                                                <th>Name</th>
                                            </tr>
                                            </thead>
                                            @foreach($authors as $author)
                                                <tr>
                                                    <td>{{ $author->role }}</td>
                                                    <td>{{ $author->name }}</td>
                                                </tr>
                                            @endforeach
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Author comments</th>
                                    <td>{!! nl2br($content?->metadata?->author_comments) !!}</td>
                                </tr>
                                <tr>
                                    <th>Source</th>
                                    <td>{{ $content?->metadata?->source }}</td>
                                </tr>
                                <tr>
                                    <th>Default language</th>
                                    <td>{{ $content?->metadata?->default_language }}</td>
                                </tr>
                                <tr>
                                    <th>Year</th>
                                    <td>{{ $content?->metadata?->year_from }} - {{ $content?->metadata?->year_to }}</td>
                                </tr>
                                <tr>
                                    <th>License</th>
                                    <td>{{ $content?->metadata?->license }}</td>
                                </tr>
                                <tr>
                                    <th>License version</th>
                                    <td>{{ $content?->metadata?->license_version }}</td>
                                </tr>
                                <tr>
                                    <th>License extras</th>
                                    <td>{!! nl2br($content?->metadata?->license_extras) !!}</td>
                                </tr>
                                <tr>
                                    <th>Changelog</th>
                                    @php
                                        $log = $content?->metadata?->changes ? json_decode($content->metadata->changes) : []
                                    @endphp
                                    <td>
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Who</th>
                                                    <th>Entry</th>
                                                </tr>
                                            </thead>
                                            @foreach($log as $item)
                                                <tr>
                                                    <td>{{ \Illuminate\Support\Carbon::createFromFormat('d-m-y H:i:s', $item->date)->toDateTimeString() }}</td>
                                                    <td>{{ $item->author }}</td>
                                                    <td>{!! nl2br($item->log) !!}</td>
                                                </tr>
                                          @endforeach
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
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
                                        <th>Version id</th>
                                        <th>Content id</th>
                                        <th>Version created</th>
                                        <th>Title</th>
                                        <th>License</th>
                                        <th>Language</th>
                                        <th>Reason</th>
                                        <th>Library</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $historyItem)
                                        @php($versionId = $requestedVersion ? $requestedVersion->id : $content->version_id)
                                        <tr>
                                            @if ($historyItem['id'] !== $versionId)
                                                <td>
                                                    <a href="{{ route('admin.content-details', [$historyItem['content_id'], $historyItem['id']]) }}">
                                                        {{ $historyItem['id'] }}
                                                    </a>
                                                </td>
                                            @else
                                                <td>{{ $historyItem['id'] }}</td>
                                            @endif
                                            <td>
                                                @if ($historyItem['content_id'] !== ((string)$content->id))
                                                    <a href="{{ route('admin.content-details', [$historyItem['content_id']]) }}">
                                                        {{ $historyItem['content_id'] }}
                                                    </a>
                                                @else
                                                    {{ $historyItem['content_id'] }}
                                                @endif
                                            </td>
                                            <td>{{ $historyItem['versionDate']->format('Y-m-d H:i:s.u e') }}</td>
                                            <td>{{ $historyItem['content']['title'] ?? '' }}</td>
                                            <td>{{ $historyItem['content']['license'] ?? '' }}</td>
                                            <td>{{ $historyItem['content']['language'] ?? '' }}</td>
                                            <td>{{ $historyItem['version_purpose'] }}</td>
                                            <td>
                                                @if(isset($historyItem['content']) && isset($historyItem['content']['library_id']))
                                                    <a href="{{ route('admin.check-library', [$historyItem['content']['library_id']]) }}">
                                                        {{ $historyItem['content']['library'] }}
                                                    </a>
                                                @else
                                                    {{ $historyItem['content']['library'] ?? '' }}
                                                @endif
                                            </td>
                                        </tr>
                                        @break($historyItem['id'] === $versionId)
                                    @endforeach
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
                                        <th>Version id</th>
                                        <th>Content id</th>
                                        <th>Version created</th>
                                        <th>Title</th>
                                        <th>License</th>
                                        <th>Language</th>
                                        <th>Reason</th>
                                        <th>Library</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php($versionId = $requestedVersion ? $requestedVersion->id : $content->version_id)
                                    @if(!empty($history[$versionId]) && !empty($history[$versionId]['children']))
                                        @foreach($history[$versionId]['children'] as $child)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.content-details', [$child['content_id'], $child['id']]) }}">
                                                        {{ $child['id'] }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.content-details', [$child['content_id']]) }}">
                                                        {{ $child['content_id'] }}
                                                    </a>
                                                </td>
                                                @isset($child['content'])
                                                    <td>{{ $child['versionDate']->format('Y-m-d H:i:s.u e') }}</td>
                                                    <td>{{ $child['content']['title'] ?? '' }}</td>
                                                    <td>{{ $child['content']['license'] ?? '' }}</td>
                                                    <td>{{ $child['content']['language'] ?? '' }}</td>
                                                    <td>{{ $child['version_purpose'] }}</td>
                                                    <td>
                                                        @isset($child['content']['library_id'])
                                                            <a href="{{ route('admin.check-library', [$child['content']['library_id']]) }}">
                                                                {{ $child['content']['library'] }}
                                                            </a>
                                                        @else
                                                            {{ $child['content']['library'] ?? '' }}
                                                        @endif
                                                    </td>
                                                @else
                                                    <td colspan="6"></td>
                                                @endisset
                                            </tr>
                                        @endforeach
                                    @endif
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
                            <tr>
                                <th>Id</th>
                                <th>Machine name</th>
                                <th>Version</th>
                                <th>Weight</th>
                                <th>Dependency type</th>
                            </tr>
                            @foreach($libraries as $cLib)
                                <tr @class(['alert-danger' => $cLib['name'] === null])>
                                    @isset($cLib['name'])
                                        <td>
                                            <a href="{{ route('admin.check-library', [$cLib['id']]) }}">
                                                {{ $cLib['id'] }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.check-library', [$cLib['id']]) }}">
                                                {{ $cLib['name'] }}
                                            </a>
                                        </td>
                                    @else
                                        <td>
                                            {{ $cLib['id'] }}
                                        </td>
                                        <td>
                                            -- Not found --
                                        </td>
                                    @endisset
                                    <td>{{ $cLib['version'] }}</td>
                                    <td>{{ $cLib['weight'] }}</td>
                                    <td>{{ $cLib['dependency_type'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
