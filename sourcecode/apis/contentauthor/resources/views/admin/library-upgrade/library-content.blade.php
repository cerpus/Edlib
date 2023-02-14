@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            Contents of type "<strong>{{ $library->name }}</strong>" version
                            <strong>{{ $library->major_version . '.' . $library->minor_version . '.' . $library->patch_version}}</strong>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="panel-body row">
                            <div class="alert alert-info">
                                By copying the Folium id and pasting it in the search field in Content Explorer,
                                you can find the <i>latest</i> version of the content.
                            </div>
                            <div class="alert alert-warning">
                                Some content listet below could be previous versions of content that has been updated
                                to a newer version of the content type
                            </div>
                        </div>
                        <div class="panel-body row">
                            @foreach($contents as $idx => $group)
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5>
                                            Folium id:
                                            <input
                                                class="ca-admin-folium-view"
                                                value="{{ $idx }}"
                                                readonly
                                                size="36"
                                            />
                                        </h5>
                                    </div>
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Id</th>
                                            <th>Title</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th>Language</th>
                                            <th>License</th>
                                            <th>Published</th>
                                            <th>Listed</th>
                                            <th>Has lock</th>
                                        </tr>
                                        @foreach($group as $content)
                                            <tr>
                                                <td>{{ $content->id }}</td>
                                                <td>{{ $content->title }}</td>
                                                <td>{{ $content->created_at->format('Y-m-d H:i:s e') }}</td>
                                                <td>{{ $content->updated_at->format('Y-m-d H:i:s e') }}</td>
                                                <td>{{ $content->language_iso_639_3 }}</td>
                                                <td>{{ $content->license }}</td>
                                                <td>{{ $content->isPublished() ? 1 : 0 }}</td>
                                                <td>{{ $content->isListed() ? 1 : 0 }}</td>
                                                <td>{{ $content->hasLock() ? 1 : 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endforeach
                        </div>

                        @if (count($failed) > 0)
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4>Content that failed when getting the Folium id</h4>
                                </div>
                                @foreach($failed as $idx => $group)
                                    <div class="panel-body row">
                                        <div class="panel panel-default">
                                            <div class="panel-heading">
                                                <h5>Error message: {{ $idx }}</h5>
                                            </div>
                                            <table class="table table-striped">
                                                <tr>
                                                    <th>Id</th>
                                                    <th>Title</th>
                                                    <th>Created</th>
                                                    <th>Updated</th>
                                                    <th>Language</th>
                                                    <th>License</th>
                                                    <th>Published</th>
                                                    <th>Listed</th>
                                                    <th>Has lock</th>
                                                </tr>
                                                @foreach($group as $content)
                                                    <tr>
                                                        <td>{{ $content->id }}</td>
                                                        <td>{{ $content->title }}</td>
                                                        <td>{{ $content->created_at->format('Y-m-d H:i:s e') }}</td>
                                                        <td>{{ $content->updated_at->format('Y-m-d H:i:s e') }}</td>
                                                        <td>{{ $content->language_iso_639_3 }}</td>
                                                        <td>{{ $content->license }}</td>
                                                        <td>{{ $content->isPublished() ? 1 : 0 }}</td>
                                                        <td>{{ $content->isListed() ? 1 : 0 }}</td>
                                                        <td>{{ $content->hasLock() ? 1 : 0 }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
