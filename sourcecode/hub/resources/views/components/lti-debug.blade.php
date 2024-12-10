@php use Carbon\CarbonImmutable; @endphp
<dl>
    @isset ($url)
        <dt>LTI launch URL</dt>
        <dd><kbd>{{ $url }}</kbd></dd>
    @endisset

    @if (!empty($parameters))
        <dt>LTI parameters</dt>
        <dd>
            <dl>
                @foreach ($parameters as $key => $value)
                    <dt>{{ $key }}</dt>
                    <dd>
                        <kbd>{{ $value }}</kbd>

                        @if ($key === 'oauth_timestamp')
                            @php($timestamp = CarbonImmutable::createFromTimestamp((int) $value))
                            <time datetime="{{ $timestamp->format('c') }}">
                                ({{ $timestamp }})
                            </time>
                        @endif
                    </dd>
                @endforeach
            </dl>
        </dd>
    @endif
</dl>
