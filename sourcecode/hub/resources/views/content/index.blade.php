<x-layout>
    <x-slot:title>{{ trans('My content!') }}</x-slot:title>

    <form action="" method="GET">
        <p>
            <input
                type="search"
                name="q"
                value="{{ $query }}"
                placeholder="Keywords"
                aria-label="{{ trans('messages.search') }}"
            >
            <button aria-label="search">🔍 {{ trans('messages.search') }}</button>
        </p>
    </form>

    <div class="grid">
        @foreach ($contents as $content)
            <x-content-card :content="$content" />
        @endforeach
    </div>

    {{ $contents->links() }}
</x-layout>
