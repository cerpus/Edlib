@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>
                            Migrate the contents of library <code>H5P.NDLAThreeImage 0.5.x</code> to <code>H5P.EscapeRoom 0.7.x</code>
                        </h3>
                    </div>
                    <div class="panel-body">
                        H5P.NDLAThreeImage 0.5.x: @if($fromLibrary) <a href="{{ route('admin.check-library', [$fromLibrary['id']]) }}" target="_blank">{{ $fromLibrary->getLibraryString(true) }}</a> @else <code>Not found</code> @endif
                        <p>
                        H5P.EscapeRoom 0.7.x: @if($toLibrary) <a href="{{ route('admin.check-library', [$toLibrary['id']]) }}" target="_blank">{{ $toLibrary->getLibraryString(true) }}</a> @else <code>Not found</code> @endif
                    </div>
                    @if ($toLibrary && $fromLibrary)
                        @if (count($migrated) > 0)
                            <div class="panel-body">
                                <h4>Migrated content</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Old content id</th>
                                            <th>New content id</th>
                                            <th>Title</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($migrated as $oldId => $newContent)
                                            <tr>
                                                <td><a href="{{ route('admin.content-details', [$oldId]) }}">{{ $oldId }}</a></td>
                                                <td>
                                                    @if($newContent['id'])
                                                        <a href="{{ route('admin.content-details', [$newContent['id']]) }}">{{ $newContent['id'] }}</a>
                                                    @endif
                                                </td>
                                                <td>{{ $newContent['title'] }}</td>
                                                <td>{{ $newContent['message'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <div class="panel-body">
                            <h4>Select content to migrate</h4>
                            {{ $paginator->onEachSide(5)->links() }}
                            Content {{ $paginator->firstItem() ?: 0 }} - {{$paginator->lastItem() ?: 0}} of {{$paginator->total()}}
                            <form method="post" enctype="multipart/form-data" id="h5p-library-migrate">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Migrate</th>
                                            <th>Content id</th>
                                            <th>Title</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paginator->getCollection() as $content)
                                            <tr>
                                                <td><input type="checkbox" name="content[]" value="{{ $content->id }}"></td>
                                                <td><a href="{{ route('admin.content-details', [$content->id]) }}">{{ $content->id }}</a></td>
                                                <td>{{ $content->title }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @csrf
                                <input type="hidden" value="{{ $fromLibrary->id }}">
                                <div id="form-group major-publishing-actions" class="submitbox">
                                    <input type="submit" name="submit" value="Migrate content" class="button button-primary button-large btn btn-primary"/>
                                </div>
                            </form>
                            {{ $paginator->onEachSide(5)->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

