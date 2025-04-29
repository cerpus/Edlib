@extends ('layouts.admin')
@section ('content')
    <div class="container">
        <a href="{{ route('admin.update-libraries') }}">Library list</a>
        <br>
        <a href="{{ route('admin.check-library', [$library->id]) }}">Library details</a>
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
                            <label>
                                <a href="{{ route('admin.content-library', [$library->id, 'listAll' => !$listAll, 'page' => $paginator->currentPage()]) }}">
                                    <input type="checkbox" {{$listAll ? '' : 'checked="checked"'}}>
                                    Only list latest version content
                                </a>
                            </label>
                        <div class="panel-body row">
                            {{ $paginator->onEachSide(5)->links() }}
                            Content {{ $paginator->firstItem() }} - {{$paginator->lastItem()}} of {{$paginator->total()}}
                            @if($listAll)
                                ( {{ $latestCount }} latest version )
                            @else
                                ( {{ $latestCount }} displayed, {{ $paginator->count() - $latestCount }} hidden )
                            @endif
                            <table class="table table-striped">
                                <tr>
                                    <th>Id</th>
                                    <th>Title</th>
                                    <th>Created</th>
                                    <th>Updated &#8595;</th>
                                    <th>Language</th>
                                    <th>License</th>
                                    <th>Latest</th>
                                </tr>
                                @foreach($paginator->getCollection() as $content)
                                    @if($listAll || $content['isLatest'] === true)
                                        <tr>
                                            <td><a href="{{ route('admin.content-details', [$content['item']->id]) }}">{{ $content['item']->id }}</a></td>
                                            <td>{{ $content['item']->title }}</td>
                                            <td>{{ $content['item']->created_at->format('Y-m-d H:i:s e') }}</td>
                                            <td>{{ $content['item']->updated_at->format('Y-m-d H:i:s e') }}</td>
                                            <td>{{ $content['item']->language_iso_639_3 }}</td>
                                            <td>{{ $content['item']->license }}</td>
                                            <td>{{ $content['isLatest'] !== null ? ($content['isLatest'] ? 'Yes' : 'No') : '' }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                            {{ $paginator->onEachSide(5)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
