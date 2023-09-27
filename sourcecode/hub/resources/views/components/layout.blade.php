<x-layout.base :nav="$nav ?? true">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @if (\Illuminate\Support\Facades\Route::current()->getName() === 'content.mine' || \Illuminate\Support\Facades\Route::current()->getName() === 'content.index')
                <x-sidebar />
            @endif
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
