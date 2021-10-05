@extends('layouts.admin')

@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif
        <div class="row" style="margin-bottom: 1em;">
            <div class="col-md-10 col-md-offset-1">
                <form method="GET" action="{{ route("admin.recommendation-engine.search") }}"
                      class="form-inline center">
                    <input class="form-control" name="query" value="{{ old("query") }}">
                    <input type="submit" class="form-control btn btn-default" value="Search">
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Search for content</div>
                    <table class="table table-striped table-hover">
                        <tr>
                            <td>Title</td>
                            <th>Created</th>
                            <th>Version Id</th>
                            <th>In Recommendation Engine</th>
                            <th>Actions</th>
                        </tr>
                        <tbody>
                        @forelse($results as $result)
                            <tr>
                                <td>[{{ $result->type }}] {{ $result->title }}</td>
                                <td>{{ $result->created_at->format("Y.m.d H:m:i") }}</td>
                                <td>{{ $result->version_id ?? "-?-" }}</td>
                                <td>{{ $result->in_re ?? false ? "Yes" : "No" }}</td>
                                <td>
                                    @if($result->in_re)
                                        <a href="{{ route("admin.recommendation-engine.remove", ["id"=> $result->id, "query" => old("query")]) }}"
                                           class="btn btn-sm btn-danger">Remove</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Try a search...</td>
                            </tr>
                        @endforelse
                        </tbody>
                </div>
            </div>
        </div>
    </div>
@endsection

