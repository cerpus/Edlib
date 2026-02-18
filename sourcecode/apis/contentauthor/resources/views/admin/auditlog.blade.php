@extends('layouts.admin')

@section('content')
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3>
                    Audit log
                </h3>
            </div>
        <div class="panel-body">
            Content {{ $entries->firstItem() ?: 0 }} - {{$entries->lastItem() ?: 0}} of {{$entries->total()}}
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Time</th>
                        <th>User</th>
                        <th>User Id</th>
                        <th>Action</th>
                        <th>Content</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>{{ $entry->id }}</td>
                        <td>{{ $entry->created_at }}</td>
                        <td>{{ $entry->user_name }}</td>
                        <td>{{ $entry->user_id }}</td>
                        <td>{{ $entry->action }}</td>
                        <td><samp>{{ $entry->content }}</samp></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{ $entries->links() }}
        </div>
    </div>
@endsection
