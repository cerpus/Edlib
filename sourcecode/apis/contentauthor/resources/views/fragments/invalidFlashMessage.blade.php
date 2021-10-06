@if(Session::has('invalidMessage'))
    <div class="alert alert-danger">
        @if(Session::has('invalidCloseButton'))
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @endif
        {!! Session::get("invalidMessage") !!}
    </div>
@endif