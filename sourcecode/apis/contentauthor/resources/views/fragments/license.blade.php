@if(config('feature.licensing') === true || config('feature.licensing') === '1')
    {!! Form::hidden("license", $license, array('id' => 'license')) !!}
    <div id="license-indicator"
         data-locale="{{ Session::get('locale', config('app.fallback_locale'))}}"
         data-owner="{{$isOwner}}"
         data-private="{{$isPrivate}}"
    ></div>
@endif
