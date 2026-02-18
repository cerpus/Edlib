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
                    <div class="panel-heading">Presave settings</div>
                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">Actions</div>
                            <div class="panel-body">
                                <button type="button" class="btn btn-default" data-toggle="modal"
                                        data-target="#runPresaveCommandModal">Run h5p:addPresave
                                </button>
                            </div>
                        </div>
                    </div>
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
                    <form method="post" action="{{ route('admin.presave.run-presave') }}">
                        {{ csrf_field() }}
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button class="btn btn-primary">Run h5p::addPresave command</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
