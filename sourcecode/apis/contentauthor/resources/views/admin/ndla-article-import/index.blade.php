@extends('layouts.admin')


@section('content')
    @if(resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->adapterIs('cerpus'))
        <div class="container">
            @if(session()->has('message'))
                <div class="row">
                    <div class="alert alert-info">{{ session('message') }}</div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-default">
                        <div class="panel-heading">Import NDLA Articles <span class="pull-right"><a
                                        href="{{ route('admin.ndla.status') }}">Import status</a></span></div>
                        <div class="panel-body">
                            <div class="pull-right">
                                <a href="{{ route('admin.ndla.refresh', ['page' => $articles->currentPage()]) }}"
                                   class="btn btn-primary pull-right"><i class="fa fa-refresh"></i></a>
                                <span class="pull-right">Articles in DB / Articles in API: <strong>{{ $articles->total() }}</strong>/ {{ $articleApiCount }}</span>
                                <span class="pull-right" style="margin-left: 8px; margin-right: 8px;">
                            <form method="get" action="{{ route('admin.ndla.search') }}" class="form-inline">
                                {{csrf_field()}}
                                <input type="text" name="query" value="{{old('query')}}" placeholder="Word* or ID"
                                       class="form-control">
                                <button type="submit" class="btn btn-default">Search</button>
                            </form>
                                </span>
                            </div>
                            <form action="{{ route('admin.ndla.multi-import') }}" method="post">
                                {{ csrf_field() }}
                                <input type="submit" class="btn btn-default" style="margin-bottom: 16px;"
                                       value="Import selected">
                                <table class="table table-striped table-hover" style="width:100%;">
                                    <tbody>
                                    <tr>
                                        <th>NDLA ID</th>
                                        <th>CA ID</th>
                                        <th>Translations</th>
                                        <th>Title</th>
                                    </tr>
                                    @forelse($articles as $article)
                                        <tr>
                                            <td style="width:9%;">
                                                <input type="checkbox" name="import[]" value="{{ $article->id }}">
                                                <a href="{{ route('admin.ndla.show',  $article->id) }}"
                                                   class="btn btn-link">
                                                    {{ $article->id }}</a>
                                            </td>
                                            <td style="width:5%;text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                                @if($article->ca_id)
                                                    <a href="{{ $article->ca_id ? route('article.show', $article->ca_id):'#' }}"
                                                       target="_blank"
                                                       class="btn btn-link" {{ $article->ca_id ? '' : 'disabled="disabled"' }}>
                                                        {{ $article->ca_id }}</a>
                                                    <a href="{{ $article->ca_id ? route('article.edit', $article->ca_id):'#' }}"
                                                       target="_blank"
                                                       class="btn btn-success" {{ $article->ca_id ? '' : 'disabled="disabled"' }}>
                                                        <i class="fa fa-edit"></i></a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @forelse($article->translations as $translation)
                                                    <a href="{{ route('article.show', $translation->content->id) }}"
                                                       target="_blank"
                                                       class="btn btn-link">{{ $translation->language }}</a>
                                                @empty
                                                    -
                                                @endforelse
                                            </td>
                                            <td style="width:36%;text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                                <a href="{{ route('admin.ndla.show',  $article->id) }}"
                                                   class="btn btn-link">
                                                    {{ $article->title }}</a>
                                                <span class="pull-right">
                                        @if( $article->ca_id)
                                                        <a href="{{ route('admin.ndla.import', [$article->id, 'page' => $articles->currentPage()]) }}"
                                                           class="btn btn-default"><i class="fa fa-refresh"></i></a>
                                                        <a href="{{ route('admin.ndla.delete', [$article->id]) }}"
                                                           class="btn btn-default"><i class="fa fa-trash"></i></a>
                                                    @else
                                                        <a href="{{ route('admin.ndla.import', [$article->id, 'page' => $articles->currentPage()]) }}"
                                                           class="btn btn-default"><i class="fa fa-cloud-download"></i></a>
                                                        <a href="#"
                                                           class="btn btn-default" disabled="disabled"><i
                                                                    class="fa fa-trash"></i></a>
                                                    @endif
                                        </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">
                                                <h3>No NDLA content</h3>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                                {{ $articles->appends(['query' => old('query')])->links() }}
                            </form>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal"
                            data-target="#importAllModal">
                        Import all content
                    </button>
                </div>
            </div>
        </div>

        <!-- Import All Modal -->
        <div class="modal fade" id="importAllModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Really import all NDLA articles?</h4>
                    </div>
                    <div class="modal-body">
                        <p>This will take a long time and use a lot of resources in EdLib and NDLA.</p>
                        <p>Once started it cannot be stopped.</p>
                        <p>You might instead consider:
                        <ul>
                            <li><strong>Bulk import some, but not all articles</strong> by selecting the checkboxes in
                                the
                                left column and
                                clicking the "Import selected" button.
                            </li>
                            <li><strong>Import/update articles one by one</strong> using the cloud <i
                                        class="fa fa-cloud-download"></i> or refresh <i
                                        class="fa fa-refresh"></i> icons in the right column.
                            </li>
                        </ul>
                        </p>
                        <p>
                            Make sure the H5Ps are already imported.
                        </p>
                        <p>All articles will be owned by this user:
                        <p>
                            {{ $owner->displayName ?? 'NoName' }} (<a
                                    href="mailto:{{$owner->email?? 'NoEmail'}}">{{$owner->email??'NoEmail'}}</a>)
                        </p>
                        <p>
                            AuthId: {{ $owner->id }}
                        </p>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <form action="{{ route('admin.ndla.all') }}" method="post">
                            {{ csrf_field() }}
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                No, get me out of here!
                            </button>
                            <button type="submit" class="btn btn-lg btn-danger">Yes, I'm sure.</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1>This is not the environment i expected!</h1>
    @endif
@endsection
