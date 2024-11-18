<div>
<iframe src="{{ $src }}" title="{{ $title }}" width="800" height="600" allowfullscreen id="edlib-{{ $id }}"></iframe>
<script>
    addEventListener('message', (event) => {
        const frame = document.getElementById('edlib-{{ $id }}');
        if (event.source !== frame.contentWindow) {
            return;
        }
        if (event?.data?.action !== 'resize') {
            return;
        }
        frame.height = String(
            event.data.scrollHeight +
            frame.getBoundingClientRect().height -
            frame.scrollHeight,
        );
    });
</script>
</div>
