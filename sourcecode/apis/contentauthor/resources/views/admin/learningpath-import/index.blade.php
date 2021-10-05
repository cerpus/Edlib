@extends('layouts.admin')

@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Import Learning Paths</div>
                    <div class="panel-body">
                        <div class="pull-right">
                            <a href="{{ route('admin.learningpath.sync', ['page' => $learningPaths->currentPage()]) }}"
                               class="btn btn-primary pull-right"><i class="fa fa-exchange"></i></a>
                            <span class="pull-right">LP in DB / LP in API: <strong>{{ $learningPaths->total() }}</strong>/ {{ $learningPathsApiCount }}</span>
                        </div>
                        {{--                         <form action="{{ route('admin.ndla.multi-import') }}" method="post">
                                                    {{ csrf_field() }}
                                                    <input type="submit" class="btn btn-default" style="margin-bottom: 16px;"
                                                           value="Import selected">
                                                           --}}
                        <table class="table table-striped table-hover" style="width:100%;">
                            <tbody>
                            <tr>
                                <th>Learning Path ID</th>
                                <th>Translations</th>
                                <th>Title</th>
                            </tr>
                            @forelse($learningPaths as $learningPath)
                                <tr>
                                    <td style="width:9%;">
                                        {{-- <input type="checkbox" name="import[]" value="{{ $learningPath->id }}"> --}}
                                        <a href="{{ route('admin.learningpath.show',  $learningPath->id) }}"
                                           class="btn btn-link">
                                            {{ $learningPath->id }}</a>
                                    </td>
                                    <td>
                                        {{--
                                        @forelse($learningPath->translations as $translation)
                                            <a href="{{ route('learningpath.show', $translation->content->id) }}"
                                               target="_blank" class="btn btn-link">{{ $translation->language }}</a>
                                        @empty
                                            -
                                        @endforelse
                                        --}}
                                        -
                                    </td>
                                    <td style="width:36%;text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                                        <a href="{{ route('admin.learningpath.show',  $learningPath->id) }}"
                                           class="btn btn-link">
                                            {{ $learningPath->title }}</a>
                                        <span class="pull-right">
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <h3>No Learning Paths</h3>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        {{ $learningPaths->appends(['query' => old('query')])->links() }}
                        {{-- </form> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
