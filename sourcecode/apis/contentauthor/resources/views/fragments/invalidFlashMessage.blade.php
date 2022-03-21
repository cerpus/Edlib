@if(Session::has('invalidMessages'))
    <div class="alert alert-danger">
        @if(Session::has('invalidCloseButton'))
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @endif

        <ul>
            @foreach (Session::get('invalidMessages') as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
