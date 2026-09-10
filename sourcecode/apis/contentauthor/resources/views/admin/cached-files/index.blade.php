@extends('layouts.admin')

@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        @if($isJobRunning)
            <div class="row">
                <div class="alert alert-warning" id="jobRunningAlert">
                    <div class="pull-right">
                        <button type="button" class="btn btn-warning btn-xs" data-toggle="modal"
                                data-target="#unlockCachedFilesModal">
                            Remove lock
                        </button>
                    </div>
                    <i class="glyphicon glyphicon-refresh glyphicon-spin"></i>
                    A deletion job is currently running in the background. Please wait...
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Cached files management</div>
                    <div class="panel-body">
                        <p>
                            Manage aggregated H5P library asset files and cached bundles.
                        </p>
                        <ul>
                            <li>Cached asset files on disk: <strong>{{ $cachedFilesCount }}</strong></li>
                            <li>Cached asset database records: <strong>{{ $cachedAssetRowsCount }}</strong></li>
                        </ul>
                        <div class="panel panel-default">
                            <div class="panel-heading">Actions</div>
                            <div class="panel-body">
                                <button type="button" class="btn btn-danger" data-toggle="modal"
                                        data-target="#deleteCachedFilesModal"
                                        @if($isJobRunning) disabled @endif>
                                    {{ $isJobRunning ? 'Deletion in progress...' : 'Delete cached files' }}
                                </button>
                                @if($isJobRunning)
                                    <button type="button" class="btn btn-default" data-toggle="modal"
                                            data-target="#unlockCachedFilesModal">
                                        Remove lock
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCachedFilesModal" tabindex="-1" role="dialog"
         aria-labelledby="deleteCachedFilesModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="deleteCachedFilesModalLabel">Delete cached files?</h4>
                </div>
                <div class="modal-body">
                    <p>
                        This will delete all aggregated library assets and cached asset bundles. They will be automatically regenerated on demand when content is viewed.
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.cached-files.delete') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-danger">Delete cached files</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($isJobRunning)
        <div class="modal fade" id="unlockCachedFilesModal" tabindex="-1" role="dialog"
             aria-labelledby="unlockCachedFilesModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="unlockCachedFilesModalLabel">Remove deletion lock?</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                            Are you sure you want to remove the deletion lock? You should only do this if a previous deletion job failed or got stuck.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <form method="post" action="{{ route('admin.cached-files.unlock') }}">
                            {{ csrf_field() }}
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button class="btn btn-warning">Remove lock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@if($isJobRunning)
    @push('js')
        <script>
            (function () {
                const checkStatus = setInterval(function () {
                    fetch("{{ route('admin.cached-files.status') }}")
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (!data.isRunning) {
                                clearInterval(checkStatus);
                                window.location.reload();
                            }
                        })
                        .catch(function (err) {
                            console.error('Error polling status:', err);
                        });
                }, 3000);
            })();
        </script>
    @endpush
@endif
