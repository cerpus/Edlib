<x-layout.base :nav="$nav ?? true">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <x-sidebar />
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <header>
                    <h1 class="fs-2">{{ $title }}</h1>
                </header>
                <!-- main content -->
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layout.base>
