@if( !empty($upgradeList) && config('h5p.singleContentUpgrade') === true)
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                {{ trans('h5p-editor.upgrade-content') }}
            </div>
            <div class="panel-body">
                <div id="content-upgrade-container" data-libraries="{{$upgradeList}}"></div>
            </div>
        </div>
    </div>
@endif
@push('js')
    {!! $adminConfig !!}
@endpush