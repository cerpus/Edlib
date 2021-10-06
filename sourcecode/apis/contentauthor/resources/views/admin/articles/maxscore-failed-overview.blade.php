@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Failed calculations <span class="badge">{{count($resources)}}</span></div>
                    <div class="panel-body">
                        <p>Articles that caused errors during bulk operation</p>
                        <table class="table">
                            <th>ID</th>
                            <th>Title</th>
                            <th>Created</th>
                            <th>Owner</th>
                        @forelse($resources as $resource)
                            <tr>
                                <td>{{$resource->id}}</td>
                                <td>{{$resource->title}}</td>
                                <td>{{$resource->created_at}}</td>
                                <td>{{$resource->ownerName}}</td>
                            </tr>
                        @empty
                            No articless found
                        @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
