@if ($canPublish)
    <div>
        {!! Form::hidden("license", $license, array('id' => 'license')) !!}
        <div
                id="share-indicator"
                data-owner="{{$isOwner}}"
                data-published="{{$isPublished}}"
                data-private="{{$isPrivate}}"
                data-license="{{$license}}"
                data-locale="{{ Session::get('locale', config('app.fallback_locale'))}}">
        </div>
    </div>
@else
    @include('fragments.license')
@endif
