<x-layout.base :nav="$nav ?? true">
    <main class="container">
        <header>
            <h1 class="fs-2">{{ $title }}</h1>
        </header>

        {{ $slot }}
    </main>
</x-layout.base>