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
                    <div class="panel-heading">Import/Export settings</div>
                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">Actions</div>
                            <div class="panel-body">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target="#resetTrackingModal">Reset NDLA ID tracking
                                </button>

                                <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target="#emptyArticleImprtLogModal">Empty NDLA Article Import Log
                                </button>

                                <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target="#runPresaveCommandModal">Run h5p:addPresave
                                </button>

                                <a href="{{route('admin.logs')}}" class="btn btn-default">Show Laravel log</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetTrackingModal" tabindex="-1" role="dialog"
         aria-labelledby="resetTrackingModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Reset NDLA ID tracking</h4>
                </div>
                <div class="modal-body">
                    <p>
                        During development or in the test environment you can empty the NDLA ID tracking.
                    </p>
                    <p>
                        This means you can start over all the imports with a blank slate.
                    </p>
                    <p>
                        Be aware that this will create new articles and you will see duplicates in content explorer!
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.importexport.reset-tracking') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="reset_ndla_id_tracking"
                               value="Reset NDLA Id tracking">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="emptyArticleImprtLogModal" tabindex="-1" role="dialog"
         aria-labelledby="emptyArticleImprtLogModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Empty Article Import Log</h4>
                </div>
                <div class="modal-body">
                    <p>
                        If the Article Import log get too large and slow you can empty the log here.
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.importexport.empty-article-import-log') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="reset_ndla_id_tracking"
                               value="Empty NDLA Article Import Log">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="runPresaveCommandModal" tabindex="-1" role="dialog"
         aria-labelledby="runPresaveCommandModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Run h5p:addPresave command?</h4>
                </div>
                <div class="modal-body">
                    <p>
                        This will add a pre-save step to h5ps that does not have it yet, and will enable maxScore on
                        older versions of H5P libraries.
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="post" action="{{ route('admin.importexport.run-presave') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="reset_ndla_id_tracking"
                               value="Run h5p::addPresave command">
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
