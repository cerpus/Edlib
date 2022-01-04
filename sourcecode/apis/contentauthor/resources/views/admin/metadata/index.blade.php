@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Resources without metadata</div>
                    <div class="panel-body">
                        <p>
                            Number of resources: {{$numMissingMetadata}}
                        </p>
                        <div class="form-group">
                            <label for="idList" class="col-form-label">IDs</label>
                            <input class="form-control" id="idList" placeholder="Comma separated list of id's">
                        </div>
                        <div class="progress-container hidden">
                            <div class="progress" data-inprogress="0" data-success="0" data-failed="0">
                                <div class="progress-bar progress-bar-success"></div>
                                <div class="progress-bar progress-bar-warning progress-bar-striped"></div>
                                <div class="progress-bar progress-bar-danger"></div>
                            </div>
                            <div>
                                <span class="label label-primary">{{$numMissingMetadata}}</span>
                                <span class="label label-success">0</span>
                                <span class="label label-warning">0</span>
                                <span class="label label-danger">0</span>
                            </div>
                        </div>
                        <a class="btn btn-info pull-right" id="logbutton" href="{{$downloadLink}}">Download log</a>
                        <button type="submit" class="btn btn-primary pull-right" id="processSwitch">Start</button>
                        <div class="error-container hidden">
                            <h3>Error messages</h3>
                            <ul class="failed-container list-group"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{mix('js/metadata.js')}}"></script>
    <script type="text/javascript">
        (function(){
            $("#processSwitch").on('click', () => {
                const rows = parseInt('{{$numRowsToTraverse ?? 0}}');
                const panelBody = $(".panel-body");
                panelBody.trigger('start', {
                    url: '{{$updateRoute}}',
                    numRowsToTraverse: rows,
                    numTotal: '{{$numMissingMetadata}}',
                }).on('addFailed', (element, data) => {
                    const failedContainer = panelBody.find('.failed-container');
                    data.failed.forEach(failed => {
                        failedContainer.append('<li class="list-group-item list-group-item-danger" style="margin-bottom: 10px">' +
                            '<div><label>Ndla id:</label><span>' + failed.ndla_id + '</span></div>' +
                            '<div><label>CA id:</label><span>' + failed.ca_id + '</span></div>' +
                            '<div><label>Error code:</label><span>' + failed.errorCode + '</span></div>' +
                            '<div><label>Error message:</label><span>' + failed.errorMessage + '</span></div>' +
                            '</li>');
                    });
                });
            });
        })();
    </script>
@endpush
