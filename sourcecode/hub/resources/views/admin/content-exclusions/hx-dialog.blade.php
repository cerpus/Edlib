@php
    $isExcluded = $content->exclusions->contains('exclude_from', 'library_translation_update');
@endphp

<x:modal>
    <x-slot:title>Content exclusions</x-slot:title>

    <div class="d-flex align-items-center justify-content-between">
        <span>Content type translation update</span>
        @if($isExcluded)
            <form method="POST" action="{{ route('admin.content-exclusions.remove-one', [$content]) }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="excludeFrom" value="library_translation_update">
                <button type="submit" class="btn btn-sm btn-outline-danger">Remove exclusion</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.content-exclusions.add-one', [$content]) }}">
                @csrf
                <input type="hidden" name="excludeFrom" value="library_translation_update">
                <button type="submit" class="btn btn-sm btn-warning">Exclude</button>
            </form>
        @endif
    </div>
</x:modal>
