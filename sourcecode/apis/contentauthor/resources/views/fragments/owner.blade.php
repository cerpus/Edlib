<div>
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ trans('h5p-editor.owner-title') }}
        </div>
        <div class="panel-body">
            <div
                    id="owner-indicator"
                    data-private="{{$isPrivate}}"
                    data-locale="{{ Session::get('locale', config('app.fallback_locale'))}}"
                    data-ownerFirstName="{{$ownerFirstName}}"
                    data-ownerLastName = "{{$ownerLastName}}"
                    data-ownerEmail = "{{$ownerEmail}}"
            ></div>
        </div>
    </div>
</div>