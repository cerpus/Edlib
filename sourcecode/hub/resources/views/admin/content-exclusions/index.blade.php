<x-layout>
    <x-slot:title>Content exclusions</x-slot:title>

    <x-admin.back-link />

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button
                @class(['nav-link', 'active' => $activeTab === 'tabExcluded'])
                data-bs-toggle="tab"
                data-bs-target="#tabExcluded"
                type="button"
                role="tab"
            >
                Excluded content
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button
                @class(['nav-link', 'active' => $activeTab === 'tabFind'])
                data-bs-toggle="tab"
                data-bs-target="#tabFind"
                type="button"
                role="tab"
            >
                Find content
            </button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- Excluded content tab --}}
        <div
            id="tabExcluded"
            @class(['tab-pane fade', 'show active' => $activeTab === 'tabExcluded'])
            role="tabpanel"
        >
            <p>
                Showing {{ $excluded->firstItem() ?: 0 }}&ndash;{{ $excluded->lastItem() ?: 0 }}
                of {{ $excluded->total() }}
            </p>

            {{ $excluded->links() }}

            <x-form method="DELETE" action="{{ route('admin.content-exclusions.delete') }}">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Content ID</th>
                            <th>Title</th>
                            <th>Excluded from</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($excluded as $exclusion)
                            <tr>
                                <td>
                                    <input
                                        type="checkbox"
                                        name="contentIds[]"
                                        value="{{ $exclusion->content_id }}"
                                    >
                                </td>
                                <td>
                                    @if($exclusion->content)
                                        <a href="{{ route('content.details', [$exclusion->content]) }}">
                                            {{ $exclusion->content_id }}
                                        </a>
                                    @else
                                        {{ $exclusion->content_id }}
                                    @endif
                                </td>
                                <td>{{ $exclusion->content?->latestPublishedVersion?->title ?? '' }}</td>
                                <td>
                                    {{ match($exclusion->exclude_from) {
                                        'content_bulk_upgrade' => 'Content type version update',
                                        'library_translation_update' => 'Content type translation update',
                                        default => $exclusion->exclude_from,
                                    } }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No excluded content</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <input type="hidden" name="excludeFrom" value="{{ $excluded->first()?->exclude_from ?? 'library_translation_update' }}">

                <x-form.button class="btn-danger" :disabled="$excluded->isEmpty()">
                    Remove selected
                </x-form.button>
            </x-form>
        </div>

        {{-- Find content tab --}}
        <div
            id="tabFind"
            @class(['tab-pane fade', 'show active' => $activeTab === 'tabFind'])
            role="tabpanel"
        >
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.content-exclusions.search') }}">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="contentId" class="form-label">Content ID:</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="contentId"
                                    id="contentId"
                                    value="{{ $searchParams['contentId'] }}"
                                    placeholder="Search by content ID"
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title:</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="title"
                                    id="title"
                                    value="{{ $searchParams['title'] }}"
                                    placeholder="Search by title (min 3 characters)"
                                    minlength="3"
                                >
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>

            @if($message)
                <div class="alert alert-warning">{{ $message }}</div>
            @endif

            @if($hasSearched)
                @if($resultsPaginator)
                    <p>
                        Showing {{ $resultsPaginator->firstItem() ?: 0 }}&ndash;{{ $resultsPaginator->lastItem() ?: 0 }}
                        of {{ $resultsPaginator->total() }}
                    </p>
                    {{ $resultsPaginator->links() }}
                @endif

                <form method="POST" action="{{ route('admin.content-exclusions.add') }}">
                    @csrf
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Content ID</th>
                                <th>Title</th>
                                <th>Language</th>
                                <th>Content type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $content)
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="contentIds[]"
                                            value="{{ $content->id }}"
                                        >
                                    </td>
                                    <td>
                                        <a href="{{ route('content.details', [$content]) }}">
                                            {{ $content->id }}
                                        </a>
                                    </td>
                                    <td>{{ $content->latestPublishedVersion?->title ?? '' }}</td>
                                    <td>{{ $content->latestPublishedVersion?->getTranslatedLanguage() ?? '' }}</td>
                                    <td>{{ $content->latestPublishedVersion?->displayed_content_type ?? '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No content found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($resultsPaginator)
                        {{ $resultsPaginator->links() }}
                    @endif

                    <div class="mb-3">
                        <label for="excludeFrom" class="form-label">
                            Exclude selected content from:
                        </label>
                        <select name="excludeFrom" id="excludeFrom" class="form-select w-auto">
                            <option value="library_translation_update" selected>Content type translation update</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" @disabled($results->isEmpty())>
                        Exclude selected
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layout>
