<div class="panel panel-default">
    <div class="panel-heading">
        <h5>
            Only the first entered value will be used, from left to right
        </h5>
    </div>
    <div class="panel-body">
        <form
            method="GET"
            action="{{ route('admin.bulkexclude.content.find') }}"
            accept-charset="UTF-8"
            enctype="multipart/form-data"
        >
            <div class="form-group col-md-4">
                <label for="contentId">CA content ID:</label>
                <input
                    class="form-control"
                    type="text"
                    name="contentId"
                    value="{{$searchParams['contentId'] ?? ""}}"
                    id="contentId"
                    placeholder="Search by Content Author content Id"
                >
            </div>
            <div class="form-group col-md-4">
                <label for="contentId">Hub content ID, version ID or URL:</label>
                <input
                    class="form-control"
                    type="text"
                    name="hubId"
                    value="{{$searchParams['hubId'] ?? ""}}"
                    id="hubId"
                    placeholder="Search by Hub content ID or URL"
                >
            </div>
            <div class="form-group col-md-4">
                <label for="contentId">Title:</label>
                <input
                    class="form-control"
                    type="text"
                    name="title"
                    value="{{$searchParams['title'] ?? ""}}"
                    id="title"
                    placeholder="Search by content title"
                    minlength="3"
                >
            </div>
            <button type="submit" class="btn btn-primary">Find</button>
        </form>
    </div>
    <div class="panel-body">
        @isset($message)
            <div class="alert alert-danger">
                {{ $message }}
            </div>
        @endisset
    </div>
</div>
<p>
    The searchresult will only list the <em>latest version</em> of a content, so the id of the content listed
    may not match the id you searched for. Click the content id in the searchresult to view content details,
    including the version history.
</p>
@isset($results)
    <div class="panel-body row">
        Content {{ $results->firstItem() ?: 0 }} - {{$results->lastItem() ?: 0}} of {{$results->total()}}
        {{ $results->onEachSide(5)->links() }}
        <form
            method="POST"
            accept-charset="UTF-8"
            enctype="multipart/form-data"
            action="{{ route('admin.bulkexclude.content.add') }}"
        >
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Content Id</th>
                        <th>Title</th>
                        <th>Language</th>
                        <th>Content type</th>
                        <th>Exclutions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{$result->id}}" name="contentIds[]">
                        </td>
                        <td>
                            <a href="{{ route('admin.content-details', [$result->id]) }}">
                                {{ $result->id }}
                            </a>
                        </td>
                        <td>{!! $result->title !!}</td>
                        <td>
                            @if($result->metadata)
                                {{ $result->metadata->default_language }}
                            @endif
                        </td>
                        <td>
                            @isset($result->library)
                                <a href="{{ route('admin.check-library', [$result->library->id]) }}">
                                    {{ $result->library->getLibraryString(true) }}
                                </a>
                            @endisset
                        </td>
                        <td>
                            @isset($result->exclutions)
                                {!!
                                    $result->exclutions->map(function ($item) {
                                        return match($item->exclude_from) {
                                            \App\ContentBulkExclude::BULKACTION_BULK_UPGRADE => 'Content type version update',
                                            \App\ContentBulkExclude::BULKACTION_LIBRARY_TRANSLATION => 'Content type translation update',
                                        };
                                    })->join('<br>')
                                !!}
                            @endisset
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5">No content found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $results->onEachSide(5)->links() }}

            <div class="panel-group">
                <label for="excludeFrom">
                    Select bulk action to exclude selected content from
                </label>
                <select name="excludeFrom" id="excludeFrom">
                    <option selected value="{{ \App\ContentBulkExclude::BULKACTION_LIBRARY_TRANSLATION }}">Content type translation update</option>
                </select>
            </div>
            <button
                type="submit"
                class="btn btn-primary"
                @disabled(!isset($results) || count($results)<1)
            >
                Exclude selected
            </button>
            @csrf
        </form>
    </div>
@endisset
