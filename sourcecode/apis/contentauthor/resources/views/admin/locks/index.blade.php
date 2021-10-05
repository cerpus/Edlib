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
                    <div class="panel-heading">Manage Edit Locks</div>
                    <table class="table table-striped">
                        <tr>
                            <th>Content title</th>
                            <th>Lock held by</th>
                            <th>Locked until</th>
                            <th>Actions</th>
                        </tr>
                        @forelse ( $locks as $lock)
                            <tr>
                                <td>{{ $lock->title }}</td>
                                <td>{{ $lock->name }} ({{ $lock->email ?? '-' }})</td>
                                <td>{{ $lock->locked_to }}</td>
                                <td>
                                    <form action="{{ route("admin.locks.delete") }}" method="POST">
                                        @csrf
                                        @method("DELETE")
                                        <input type="hidden" name="lock_id" value="{{ $lock->id }}">
                                        <input type="submit" class="btn btn-danger" value="Remove lock">
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <td colspan="4"><p>No edit locks</p></td>
                        @endforelse
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection

