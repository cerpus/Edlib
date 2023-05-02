@extends ('layouts.admin')
@section ('content')
    <div class="container">
        @php
            $contentHistory = $history[$content->id]['content'];
        @endphp
        <a href="{{ route('admin.update-libraries') }}">Back to library list</a>
        <br>
        <a href="{{ route('admin.content-library', [$contentHistory['library_id']]) }}">Back to content list</a>
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
                                <th>Folium id</th>
                                <td>{{ $foliumId }}</td>
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
                                <th>Latest version</th>
                                <td>{{ $latestVersion ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Language</th>
                                <td>{{ $content->language_iso_639_3 }}</td>
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
                                <td>{{ $content->hasLock() ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Library</th>
                                <td>{{ $contentHistory['library'] }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @empty($history)
                    <div class="alert alert-danger">
                        Failed getting history
                    </div>
                @endempty
                @isset($history)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4>History</h4>
                        </div>
                        <div class="panel-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>License</th>
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
                                                @if ($itemId !== $content->id)
                                                    <a href="{{ route('admin.content-details', [$history[$itemId]['externalReference']]) }}">
                                                        {{ $history[$itemId]['externalReference'] }}
                                                    </a>
                                                @else
                                                    {{ $history[$itemId]['externalReference'] }}
                                                @endif
                                            </td>
                                            <td>{{ $history[$itemId]['versionDate'] }}</td>
                                            <td>{{ $history[$itemId]['content']['title'] ?? '' }}</td>
                                            <td>{{ $history[$itemId]['content']['license'] ?? '' }}</td>
                                            <td>{{ $history[$itemId]['versionPurpose'] }}</td>
                                            <td>{{ $history[$itemId]['content']['library'] ?? '' }}</td>
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
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>License</th>
                                        <th>Reason</th>
                                        <th>Library</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($latestVersion)
                                        <tr>
                                            <td colspan="6">
                                                This is the latest version
                                            </td>
                                        </tr>
                                    @else
                                        @foreach($history[$content->id]['children'] as $itemId)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.content-details', [$history[$itemId]['externalReference']]) }}">{{ $history[$itemId]['externalReference'] }}</a>
                                                </td>
                                                <td>{{ $history[$itemId]['versionDate'] }}</td>
                                                <td>{{ $history[$itemId]['content']['title'] ?? '' }}</td>
                                                <td>{{ $history[$itemId]['content']['license'] ?? '' }}</td>
                                                <td>{{ $history[$itemId]['versionPurpose'] }}</td>
                                                <td>{{ $history[$itemId]['content']['library'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endisset
            </div>
        </div>
    </div>
@endsection
