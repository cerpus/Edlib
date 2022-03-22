@if($errors->any())
    <div class="alert alert-danger">
        @if(Session::has('invalidCloseButton'))
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        @endif

        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
