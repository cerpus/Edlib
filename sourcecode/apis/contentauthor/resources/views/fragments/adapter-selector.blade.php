@if( config('feature.allow-mode-switch') === true && !empty($adapterModes) )
    <div>
        <div id="adapter-selector-container" data-adapters="{{$adapterModes}}" data-current-adapter="{{$currentAdapter}}"></div>
    </div>
@endif
