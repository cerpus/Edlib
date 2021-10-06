@if( !empty($contentProperties) && config('app.displayPropertiesBox'))
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('h5p-editor.properties') }}
            </div>
            <div class="panel-body">
                <div id="resource-properties-container" data-content-properties="{{$contentProperties}}"></div>
            </div>
        </div>
    </div>
@endif
