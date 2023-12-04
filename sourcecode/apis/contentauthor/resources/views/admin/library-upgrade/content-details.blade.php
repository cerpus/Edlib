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
                                <th>Folium id</th>
                                <td>{{ $resource?->id ?? '' }}</td>
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
                                <th>Deleted</th>
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
                                                @if ($itemId !== $content->id && isset($history[$itemId]['content']))
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
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>License</th>
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
                                                        <a href="{{ route('admin.content-details', [$history[$itemId]['externalReference']]) }}">{{ $history[$itemId]['externalReference'] }}</a>
                                                    @else
                                                        {{ $history[$itemId]['externalReference'] }}
                                                    @endisset
                                                </td>
                                                <td>{{ $history[$itemId]['versionDate'] }}</td>
                                                <td>{{ $history[$itemId]['content']['title'] ?? '' }}</td>
                                                <td>{{ $history[$itemId]['content']['license'] ?? '' }}</td>
                                                <td>{{ $history[$itemId]['versionPurpose'] }}</td>
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
            </div>
        </div>
    </div>
@endsection
