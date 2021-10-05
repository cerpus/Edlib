<ul>
    @foreach($settings as $setting => $value)
        <li>
            @if(is_array($value))
                <b>{{ $setting }}</b>
                @include('admin.fragments.config-settings', ['settings' => $value])
            @else
                <b>{{ $setting }}</b>
                : {{ filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null ? $value  : ($value ? 'true' : 'false')  }}
            @endif
        </li>
    @endforeach
</ul>
