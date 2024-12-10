<div>
<iframe src="{{ $src }}" title="{{ $title }}" width="800" height="600" allowfullscreen></iframe>
<script>((f, h) => addEventListener('message', e => f &&
f.contentWindow === e.source &&
e.data && e.data.action && e.data.action === 'resize' && e.data[h] &&
(f.height = String(e.data[h] + f.getBoundingClientRect().height - f[h]))
))(document.currentScript.previousElementSibling, 'scrollHeight')</script>
</div>
