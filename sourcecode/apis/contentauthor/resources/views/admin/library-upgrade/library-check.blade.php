@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            <img style="height:3em;" src="{{ $library->getIconUrl() }}" alt="Content type icon">
                            Details for {{ $library->runnable ? 'content type' : 'library' }} <strong>{{ $library->getLibraryString(true) }}</strong>
                        </h3>
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
                            @if (array_key_exists('semantics', $libData) && trim($libData['semantics']) !== trim($library->semantics))
                                <div class="alert alert-danger">
                                    Semantics data in database does not match the 'semantics.json' file.
                                    Rebuild the library to update the database
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
                            <tr @if(isset($libData['patchVersion']) && $library->patch_version !== $libData['patchVersion']) class="alert-danger"@endif>
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
                                <td>{{ $library->created_at?->format('Y-m-d H:i:s e') }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Updated time</th>
                                <td>{{ $library->updated_at?->format('Y-m-d H:i:s e') }}</td>
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
                                <th>Patch version in folder name</th>
                                <td>{{ $library?->patch_version_in_folder_name ? 'Yes' : 'No' }}</td>
                                <td></td>
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
                            <tr>
                                <th>Translations in database ({{$languages->count()}})</th>
                                <td colspan="2">
                                    @foreach($languages as $lang)
                                        <a
                                            href="{{ route('admin.library-translation', [$library->id, $lang]) }}"
                                            class="btn btn-default"
                                            title="{{Iso639p3::englishName($lang)}}"
                                        >
                                            {{ $lang }}
                                        </a>
                                    @endforeach
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>Number of contents</th>
                                <td colspan="2">
                                    <a href="{{ route('admin.content-library', [$library->id]) }}">
                                        {{ $library->contents()->count() }}
                                    </a>
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
                                <td style="width:50%">
                                    @if(!empty($library->semantics))
                                        <textarea
                                            readonly
                                            rows="15"
                                            style="width:100%;white-space:pre"
                                        >{!!$library->semantics!!}</textarea>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($libData['semantics']))
                                        <textarea
                                            readonly
                                            rows="15"
                                            style="width:100%;white-space:pre"
                                        >{!! $libData['semantics'] !!}</textarea>
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
