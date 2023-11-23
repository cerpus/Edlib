@extends ('layouts.admin')
@section ('content')
    <div class="container" style="width:80vw">
        <a href="{{ route('admin.check-library', [$library->id]) }}">Back to library</a>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            "<b>{{ $languageCode }}</b>"
                            translation for {{ $library->getLibraryString(true) }}
                        </h3>
                    </div>
                    <div class="panel-body row">
                        <div class="alert alert-warning">
                            <ul>
                                <li>This will be a local change only, for exported content the original file is included
                                <li>If a new patch version of this library is installed, changes done may be lost
                            </ul>
                        </div>
                    </div>
                    @isset($success)
                        @if($success)
                            <div class="alert alert-success">
                                Database updated
                            </div>
                        @endif
                        @empty(!$errorMessage)
                            <div class="alert alert-danger">
                                Update failed
                                <pre style="margin-top:1em;">{{ $errorMessage }}</pre>
                            </div>
                        @endif
                    @endisset
                    @if($errors->isNotEmpty())
                        <div class="alert alert-danger">
                            Update failed
                            @foreach($errors->all() as $error)
                                <pre style="margin-top:1em;">{{ $error }}</pre>
                            @endforeach
                        </div>
                    @endif
                    <div class="panel-body row">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5>Upload language file</h5>
                            </div>
                            <div class="panel-body row">
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
                            </div>
                        </div>
                    </div>
                    <div class="panel-body row">
                        <table class="table table-striped">
                            <tr>
                                <th>Database</th>
                                <th>File (read only)</th>
                            </tr>
                            <tr>
                                <td style="width: 50%;">
                                    @empty($translationDb)
                                        No data found
                                    @else
                                        <form method="post" accept-charset="utf-8">
                                            @csrf
                                            <textarea
                                                name="translation"
                                                autocomplete="off"
                                                required
                                                style="width:100%;height:70vh;white-space:pre;"
                                            >{{$translationDb}}</textarea>
                                            <br>
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                Save
                                            </button>
                                        </form>
                                    @endempty
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
