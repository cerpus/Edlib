@if($preview && $inDraftState ?? false)
    <div class="draft-resource {{$resourceType}}" onclick="(function(element) {
        element.classList.add('hide');
    })(this)">
        {{trans('common.resource-in-draft-state')}}
        <div class="draft-resource-close" >
            {{trans('common.close')}}
        </div>
    </div>
@endif
