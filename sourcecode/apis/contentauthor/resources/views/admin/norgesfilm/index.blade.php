@extends('layouts.admin')

@inject('h5pInterface', 'App\Libraries\H5P\Interfaces\H5PAdapterInterface')

@section('content')
    @if($h5pInterface->showNorgesfilmAdmin())
        <div class="container">
            @if(session()->has('message'))
                <div class="row">
                    <div class="alert alert-info">{{ session('message') }}</div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel panel-default">
                        <div class="panel-heading">Norgesfilm Admin

                        </div>
                        <div class="panel-body">
                            <span class="pull-left">
                                <form method="GET" action="{{ route('admin.norgesfilm.index') }}" class="form-inline">
                                    <input type="text" name="search" value="{{ old('search', $search) }}"
                                           class="form-control">
                                    <input type="submit" value="search" class="btn btn-primary">
                                </form>
                            </span>
                            <span class="pull-right">
                                <a href="{{ route('admin.norgesfilm.populate') }}" class="btn btn-sm btn-success">Reset and re-populate list</a>
                            </span>
                            <table class="table table-striped table-hover" style="width:100%; margin-top: 48px;">
                                <tbody>
                                <tr>
                                    <th>Article</th>
                                    <th>URLs</th>
                                    <th>Compare</th>
                                    <th>Actions</th>
                                </tr>
                                @forelse($articles as $article)
                                    <tr>
                                        <td>{{ $article->article_title }}</td>
                                        <td>
                                            <div>Local: <a href="{{ $article->article_url  }}" target="_blank"
                                                           class="small">{{ $article->article_url }}</a></div>
                                            <div>
                                                @if($article->ndla_url)
                                                    NDLA:
                                                    <a href="{{ $article->ndla_url }}"
                                                       target="_blank">{{ $article->ndla_url }}</a>
                                                @else
                                                    NDLA URL not found. <a
                                                            href="https://ndla.no/search?query={{ urlencode($article->article_title) }}"
                                                            target="_blank" class="btn btn-sm btn-default">
                                                        Try a search on NDLA </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.norgesfilm.compare', $article) }}"
                                               class="btn btn-primary">Compare</a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.norgesfilm.replace', $article->id) }}"
                                               class="btn btn-">Replace</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <div class="text-center">
                                                <a href="{{ route('admin.norgesfilm.populate') }}" class="btn btn-lg btn-block btn-success">Populate list</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            {{ $articles->appends(['search' => $search])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1>This is not the environment i expected!</h1>
    @endif
@endsection
