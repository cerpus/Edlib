<link
    rel="alternate"
    type="application/json+oembed"
    href="{{ route('oembed', ['url' => request()->url(), 'format' => 'json']) }}"
>
<link
    rel="alternate"
    type="text/xml+oembed"
    href="{{ route('oembed', ['url' => request()->url(), 'format' => 'xml']) }}"
>
