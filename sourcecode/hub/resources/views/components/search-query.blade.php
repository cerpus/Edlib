<div class="col-md-6 col-lg-6 d-md-block d-none">
    <div class="search-query">
        @foreach (['Media', 'Interactive Video', 'Text'] as $badgeLabel)
            <span class="col-auto badge text-bg-primary">
                {{ $badgeLabel }}
                <i class="bi bi-x"></i>
            </span>
        @endforeach
    </div>
    <div class="mt-1">
        @foreach (['Cerpus Image'] as $badgeLabel)
            <span class="col-auto badge text-bg-primary">
                {{ $badgeLabel }}
                <i class="bi bi-x"></i>
            </span>
        @endforeach
    </div>
</div>
