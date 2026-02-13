<x-layout current="create">
    <x-slot:title>{{ trans('messages.create-content') }}</x-slot:title>

    <p>{{ trans('messages.select-a-content-type') }}</p>

    <div class="row">
        @foreach ($types as $type)
            <div class="col-md-4">
                <div class="card mb-3">
                    @if($type->imageUrl == null)
                        <div class="card-img-top lti-tool-image empty d-flex justify-content-center align-items-center" aria-hidden="true">
                            <h1>{{ $type->name }}</h1>
                        </div>
                    @else
                        <img class="card-img-top lti-tool-image" src="{{ $type->imageUrl }}" aria-hidden="true">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $type->name }}</h5>
                        <a href="{{ $type->url }}" class="btn btn-primary">{{ trans('messages.create') }}</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @can('admin')
        <p>
            <a href="{{ route('admin.lti-tools.index') }}" class="btn btn-outline-secondary">
                {{ trans('messages.manage-lti-tools') }}
            </a>
        </p>
    @endcan
</x-layout>
