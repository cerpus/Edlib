@extends('layouts.admin')


@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        <div class="row">
            <h1>Most recent statuses:</h1>
            <form method="get" action="{{ route('admin.ndla.status.search') }}" class="form-inline" style="margin-bottom: 8px;">
                {{csrf_field()}}
                <input type="text" name="query" value="{{old('query')}}" placeholder="Article or import id..."
                       class="form-control">
                <select name="log_level" class="form-control">
                    <option value="{{ \App\NdlaArticleImportStatus::LOG_LEVEL_DEBUG }}"{{ old('log_level') == \App\NdlaArticleImportStatus::LOG_LEVEL_DEBUG ? ' selected' : '' }}>Everything</option>
                    <option value="{{ \App\NdlaArticleImportStatus::LOG_LEVEL_ERROR }}"{{ old('log_level') == \App\NdlaArticleImportStatus::LOG_LEVEL_ERROR ? ' selected' : '' }}>Errors</option>
                </select>
                <button type="submit" class="btn btn-default">Search</button>
            </form>
            {{ $statuses->appends(request()->input())->links() }}
            <table class="table table-striped table-hover">
                <tr>
                    <th>Date</th>
                    <th>Import ID</th>
                    <th>NDLA ID</th>
                    <th>Message</th>
                </tr>
                @foreach($statuses as $status)
                    <tr class="{{ $status->log_level >= \App\NdlaArticleImportStatus::LOG_LEVEL_ERROR ? 'danger' : '' }}">
                        <td>{{ $status->updated_at->timezone('Europe/Oslo')->toDateTimeString() }}</td>
                        <td>{{ $status->import_id ?? '-' }}</td>
                        <td>{{ $status->ndla_id }}</td>
                        <td>{!! nl2br($status->message)  !!}</td>
                    </tr>
                @endforeach
            </table>
            {{ $statuses->appends(request()->input())->links() }}
        </div>
    </div>
@endsection
