@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        <a href="{{ route('admin.content-library', $content->library->id) }}">Library content list</a>
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
                                <td>{{ $content->id }}</td>
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
                                <th>Language</th>
                                <td>
                                    @isset($content->language_iso_639_3)
                                        {{ $content->language_iso_639_3 }} ({{ Iso639p3::englishName($content->language_iso_639_3) }})
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <th>License</th>
                                <td>{{ $content->license }}</td>
                            </tr>
                            <tr>
                                <th>Library</th>
                                <td>
                                    <a href="{{ route('admin.check-library', [$content->library->id]) }}">
                                        {{ sprintf('%s %d.%d.%d', $content->library->name, $content->library->major_version, $content->library->minor_version, $content->library->patch_version) }}
                                    </a>
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
                                                <a href="{{ route('admin.content-details', [$historyItem['content_id']]) }}">
                                                    {{ $historyItem['content_id'] }}
                                                </a>
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
            </div>
        </div>
    </div>
@endsection
