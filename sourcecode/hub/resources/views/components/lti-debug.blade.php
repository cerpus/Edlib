@php use Carbon\CarbonImmutable; @endphp
<dl>
    <dt>LTI launch URL</dt>
    <dd><kbd>{{ $request->getUrl() }}</kbd></dd>

    <dt>LTI parameters</dt>
    <dd>
        <dl>
            @foreach ($request->toArray() as $key => $value)
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
</dl>
