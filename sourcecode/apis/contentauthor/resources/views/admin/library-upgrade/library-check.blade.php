@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Details for {{ $library->runnable ? 'content type' : 'library' }} "<strong>{{ $library->name }}</strong>"</h3>
                    </div>

                    <div class="panel-body row">
                        @if (count($info) > 0 || count($error) > 0)
                            @foreach ($info as $msg)
                                <div class="alert alert-info">
                                    {{ $msg }}
                                </div>
                            @endforeach
                            @foreach ($error as $msg)
                                <div class="alert alert-danger">
                                    {{ $msg }}
                                </div>
                            @endforeach
                        @endif
                        @if (is_array($libData) === false)
                            <div class="alert alert-info">
                                Data from 'libary.json' and 'semantics.json' may not be displayed since the
                                library failed validation
                            </div>
                        @else
                            @if (array_key_exists('semantics', $libData) && $libData['semantics'] !== $library->semantics)
                                <div class="alert alert-danger">
                                    Semantics data in database does not match the 'semantics.json' file.
                                    Rebuild the library to update the database
                                </div>
                            @endif
                            @if ($library && $libData && (
                                    $library->major_version !== $libData['majorVersion'] ||
                                    $library->minor_version !== $libData['minorVersion'] ||
                                    $library->patch_version !== $libData['patchVersion']
                                )
                            )
                                <div class="alert alert-danger">
                                    Library version in database does not match version in 'library.json'
                                </div>
                            @endif
                        @endif

                        <table class="table table-striped">
                            <tr>
                                <th>Field</th>
                                <th>Database</th>
                                <th>library.json</th>
                            </tr>
                            <tr>
                                <th>Library id</th>
                                <td>{{ $library->id }}</td>
                            </tr>
                            <tr>
                                <th>Machine name</th>
                                <td>{{ $library->name }}</td>
                                <td>{{ $libData['machineName'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td>{{ $library->title }}</td>
                                <td>{{ $libData['title'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td></td>
                                <td>{{ $libData['description'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Major version</th>
                                <td>{{ $library->major_version }}</td>
                                <td>{{ $libData['majorVersion'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Minor version</th>
                                <td>{{ $library->minor_version }}</td>
                                <td>{{ $libData['minorVersion'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Patch version</th>
                                <td>{{ $library->patch_version }}</td>
                                <td>{{ $libData['patchVersion'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Author</th>
                                <td></td>
                                <td>{{ $libData['author'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>License</th>
                                <td></td>
                                <td>{{ $libData['license'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Content type</th>
                                <td></td>
                                <td>{{ $libData['contentType'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Embed types</th>
                                <td>{{ $library->embed_types }}</td>
                                <td>{{ implode(', ', $libData['embedTypes'] ?? [])}}</td>
                            </tr>
                            <tr>
                                <th>Created time</th>
                                <td>{{ $library->created_at->format('Y-m-d H:i:s e') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Updated time</th>
                                <td>{{ $library->updated_at->format('Y-m-d H:i:s e') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Runnable</th>
                                <td>{{ $library->runnable }}</td>
                                <td>{{ $libData['runnable'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Fullscreen</th>
                                <td>{{ $library->fullscreen }}</td>
                                <td>{{ $libData['fullscreen'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>preloadedJs</th>
                                <td>{!! str_replace(',', '<br>', $library->preloaded_js) !!}</td>
                                <td>
                                    @isset ($libData['preloadedJs'])
                                        @foreach ($libData['preloadedJs'] as $pjs)
                                            {{$pjs['path']}}
                                            @if (!$loop->last)<br>@endif
                                        @endforeach
                                    @endisset
                                </td>
                            </tr>
                            <tr>
                                <th>preloadedCss</th>
                                <td>{!! str_replace(',', '<br>', $library->preloaded_css) !!}</td>
                                <td>
                                    @isset ($libData['preloadedCss'])
                                        @foreach ($libData['preloadedCss'] as $pjs)
                                            {{$pjs['path']}}
                                            @if (!$loop->last)<br>@endif
                                        @endforeach
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Semantics</h3>
                    </div>

                    <div class="panel-body row">
                        <table class="table table-striped">
                            <tr>
                                <th>Database</th>
                                <th>semantics.json</th>
                            </tr>
                            <tr>
                                <td>
                                    @if(!empty($library->semantics))
                                        <textarea wrap="off" readonly style="width:400px;height:300px;">{!!$library->semantics!!}</textarea>
                                    @endif
                                </td>
                                <td>
                                    @if($libData && array_key_exists('semantics', $libData) && !empty($libData['semantics']))
                                        <textarea wrap="off" readonly style="width:400px;height:300px;">{!! $libData['semantics'] !!}</textarea>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Editor dependencies</h3>
                    </div>
                    @include ('admin.fragments.dependency-table', [
                        'dependencies' => $libData['editorDependencies'] ?? [],
                        'extraDependencies' => $editorDeps,
                        'haveLibData' => $libData !== false,
                    ])
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Preload dependencies</h3>
                    </div>

                    @include ('admin.fragments.dependency-table', [
                        'dependencies' => $libData['preloadedDependencies'] ?? [],
                        'extraDependencies' => $preloadDeps,
                        'haveLibData' => $libData !== false,
                    ])
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>Referenced by</h3>
                    </div>

                    <div class="panel-body row">
                        <table class="table table-striped">
                            <tr>
                                <th>DB id</th>
                                <th>Machine name</th>
                                <th>Type</th>
                                <th>Version</th>
                                <th>Dependency type</th>
                            </tr>
                            @foreach ($usedBy as $dep)
                                <tr>
                                    <td>{{ $dep->library->id }}</td>
                                    <td>
                                        <a href="{{ route('admin.check-library', [$dep->library->id]) }}">{{ $dep->library->name }}</a>
                                    </td>
                                    <td>{{ $dep->library->runnable ? 'Content type' : 'Library' }}</td>
                                    <td>{{ $dep->library->major_version . '.' . $dep->library->minor_version . '.' . $dep->library->patch_version}}</td>
                                    <td>{{ $dep->dependency_type }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
