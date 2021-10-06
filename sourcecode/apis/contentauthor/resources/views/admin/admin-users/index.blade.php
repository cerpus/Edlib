@extends('layouts.admin')

@section('content')

    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        @foreach($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endforeach
        <div class="row">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-10 mb-3 border-bottom">
                <h1 class="h2">Manage Admin users</h1>
            </div>
            <table class="table table-striped table-hover">
                <tr>
                    <td>#</td>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                @forelse($adminUsers as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->name }}</td>
                        <td>
                            <form method="post" action="{{ route('admin-users.destroy', $user) }}" id="delete_{{ $user->id }}">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                <button type="submit" data-toggle="tooltip" data-placement="top" title="Delete user?" onclick="return confirm('Really delete {{ $user->name }}?');"><span
                                            class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No Admin users</td>
                    </tr>
                @endforelse
            </table>
            <button class="btn btn-default" data-toggle="modal" data-target="#addUserModal">Add user...</button>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Create new Admin user</h4>
                </div>
                <form action="{{ route('admin-users.store') }}" method="post">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Name"
                                   value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Username" value="{{ old('username') }}">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <p>Minimum 18 characters. (Hint: Use a password manager like <a href="https://keepass.info/"
                                                                                            target="_blank">Keepass</a>)
                            </p>
                            <input type="text" class="form-control" id="password" name="password"
                                   value="{{ str_random(18) }}"
                                   placeholder="Password">
                        </div>

                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" value="Create user">
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
