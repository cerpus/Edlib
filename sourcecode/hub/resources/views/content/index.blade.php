<x-layout>
    <x-slot:title>{{ trans('My content!') }}</x-slot:title>

    <div class="grid">
        @foreach ($contents as $content)
            <x-content-card :content="$content" />
        @endforeach
    </div>

    {{ $contents->links() }}
</x-layout>
