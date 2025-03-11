@extends ('layouts.admin')
@section ('content')
    <div class="container" style="width:80vw">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        <a href="{{ route('admin.check-library', [$library->id]) }}">Library details</a>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            <b>{{ $library->getLibraryString(true) }} - {{locale_get_display_language($languageCode)}}</b> (<code>{{ $languageCode }}</code>)
                        </h3>
                    </div>
                    <div class="panel-body row">
                        <div class="alert alert-warning">
                            <ul>
                                <li>Changes done here will not be included when the library is exported
                                <li>If a new patch version of this library is installed, changes done may be lost
                            </ul>
                        </div>
                    </div>
                    @if($errors->isNotEmpty() || (isset($messages) && $messages->isNotEmpty()))
                        <div class="alert alert-danger">
                            Update failed
                            @foreach($errors->all() as $error)
                                <pre style="margin-top:1em;">{{ $error }}</pre>
                            @endforeach
                            @foreach($messages->all() as $msg)
                                <pre style="margin-top:1em;">{{ $msg }}</pre>
                            @endforeach
                        </div>
                    @elseif (isset($messages))
                        <div class="alert alert-success">
                            {{ $translationDb->updated_at->format('Y-m-d H:i:s e') }}: Database updated
                        </div>
                    @endif
                    <div class="panel-body row">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Content</h4>
                            </div>
                            <div class="panel-body row">
                                <p>
                                    Total content: {{ $totalCount }}
                                </p>
                                <p>
                                    Updatable content: {{ $updatableCount }}
                                </p>
                            </div>
                            <div class="panel-body row">
                                <a
                                    @class([
                                        'btn btn-danger',
                                        'disabled' => $updatableCount === 0,
                                    ])
                                    role="button"
                                    href="{{ route('admin.library-transation-content', [$library->id, $languageCode]) }}"
                                    @disabled($updatableCount === 0)
                                >
                                    Refresh translations in the content
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body row">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Upload new translation</h4>
                            </div>
                            <div class="panel-body row">
                                @if($translationFile)
                                    Maximum filesize is 50kB
                                    <form method="post" accept-charset="utf-8" enctype="multipart/form-data" >
                                        @csrf
                                        <input
                                            type="file"
                                            name="translationFile"
                                            accept=".json"
                                        >
                                        <br>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            Upload
                                        </button>
                                    </form>
                                @else
                                    Upload not available
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="panel-body row">
                        @if ($translationDb && $translationFile)
                            @if (json_decode($translationDb->translation, true) !== json_decode($translationFile, true))
                                <div class="alert alert-warning">
                                    Translation in database differ from that on file
                                </div>
                            @else
                                <div class="alert alert-info">
                                    Translations are the same
                                </div>
                            @endif
                        @endif
                        <table class="table table-striped">
                            <tr>
                                <th>
                                    Database (Max 51200 characters)
                                    @if($translationDb && $translationDb->updated_at)
                                        <br>Modified {{ $translationDb->updated_at->format('Y-m-d H:i:s e') }}
                                    @endif
                                </th>
                                <th>
                                    File (read only)
                                    @if($fileModified)
                                        <br>Modified {{ $fileModified->format('Y-m-d H:i:s e') }}
                                    @endif
                                </th>
                            </tr>
                            <tr>
                                <td style="width: 50%;">
                                    @if($translationDb)
                                        <form method="post" accept-charset="utf-8">
                                            @csrf
                                            <textarea
                                                name="translation"
                                                autocomplete="off"
                                                required
                                                maxlength="51200"
                                                style="width:100%;height:70vh;white-space:pre;"
                                            >{{$translationDb->translation}}</textarea>
                                            <br>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                Save
                                            </button>
                                        </form>
                                    @else
                                        No data found
                                    @endif
                                </td>
                                <td style="width: 50%;">
                                    @empty($translationFile)
                                        No data found
                                    @else
                                        <textarea
                                            autocomplete="off"
                                            readonly
                                            style="width:100%;height:70vh;white-space:pre;"
                                        >{{$translationFile}}</textarea>
                                    @endempty
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
