<article class="card content-card">
    <div class="card-body">
        <h5 class="card-title">
            <a href="{{ route('content.preview', [$content->id]) }}">
                {{ $content->latestVersion->resource->title }}
            </a>
        </h5>

        @foreach ($content->users as $user)
            <li>{{ $user->name }} ({{ $user->pivot->role }})</li>
        @endforeach
    </div>

    <nav class="card-footer">
        <a
            href="{{ route('content.edit', [$content->id]) }}"
            class="btn btn-outline-dark"
            title="{{ trans('messages.edit') }}"
        >
            <x-icon name="pencil" />
        </a>

        <form action="{{ route('content.copy', [$content->id]) }}" method="POST" class="d-inline">
            @csrf
            <button
                class="btn btn-outline-dark d-inline"
                title="{{ trans('messages.copy') }}"
            >
                <x-icon name="clipboard" />
            </button>
        </form>
    </nav>
</article>
