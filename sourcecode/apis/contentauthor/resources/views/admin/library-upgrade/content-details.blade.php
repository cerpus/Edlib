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
                                        <th>Content id</th>
                                        <th>Created</th>
                                        <th>Title</th>
                                        <th>License</th>
                                        <th>Language</th>
                                        <th>Reason</th>
                                        <th>Library</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $historyItem)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.content-details', [$historyItem['content']['id']]) }}">
                                                    {{ $historyItem['content']['id'] }}
                                                </a>
                                            </td>
                                            <td>{{ $historyItem['content']['created_at']->format('Y-m-d H:i:s e') }}</td>
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
                                        @break($historyItem['content']['id'] === $content['id'])
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
                                        <th>Content id</th>
                                        <th>Created</th>
                                        <th>Title</th>
                                        <th>License</th>
                                        <th>Language</th>
                                        <th>Reason</th>
                                        <th>Library</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history[$content->id]['children'] as $childId)
                                        @php ($child = $history[$childId])
                                        <tr>
                                            <td><a href="{{ route('admin.content-details', $childId) }}">{{ $childId }}</a>
                                            <td>{{ $child['content']['created_at']->format('Y-m-d H:i:s e') }}</td>
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endempty
            </div>
        </div>
    </div>
@endsection
