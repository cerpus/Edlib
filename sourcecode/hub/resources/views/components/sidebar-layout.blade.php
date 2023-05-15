<x-layout.base>
    <div class="container">
        <div class="row">
            <main class="col-12 col-lg-9">
                <header>
                    <h1 class="fs-2">{{ $title }}</h1>
                </header>

                {{ $slot }}
            </main>

            <aside class="col-12 col-lg-3">
                {{ $sidebar ?? '' }}
            </aside>
        </div>
    </div>
</x-layout.base>
