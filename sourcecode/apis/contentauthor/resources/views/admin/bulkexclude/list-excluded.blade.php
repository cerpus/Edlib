<div class="panel-body">
    Content {{ $contents->firstItem() ?: 0 }} - {{$contents->lastItem() ?: 0}} of {{$contents->total()}}
    {{ $contents->onEachSide(5)->links() }}

    <form
        method="POST"
        accept-charset="UTF-8"
        enctype="multipart/form-data"
        action="{{ route('admin.bulkexclude.content.delete') }}"
    >
        @method('DELETE')
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Content id</th>
                    <th>Title</th>
                    <th>Language</th>
                    <th>Content type</th>
                    <th>Excluded from</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contents as $content)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{$content->id}}" name="excludeIds[]">
                        </td>
                        <td>
                            <a href="{{ route('admin.content-details', [$content->content_id]) }}">
                                {{ $content->content_id }}
                            </a>
                        </td>
                        <td>{{$content->content->title}}</td>
                        <td>{{$content->content->metadata->default_language}}</td>
                        <td>
                            @isset($content->content->library)
                                <a href="{{ route('admin.check-library', [$content->content->library->id]) }}">
                                    {{ $content->content->library->getLibraryString(true) }}
                                </a>
                            @else
                                -
                            @endisset
                        </td>
                        <td>
                            {{
                                match($content->exclude_from) {
                                    \App\ContentBulkExclude::BULKACTION_BULK_UPGRADE => 'Content type version update',
                                    \App\ContentBulkExclude::BULKACTION_LIBRARY_TRANSLATION => 'Content type translation update',
                                }
                            }}
                        </td>
                    </tr>
                @empty
                    No content
                @endforelse
            </tbody>
        </table>
        @csrf
        <button
            type="submit"
            class="btn btn-danger"
            @disabled(!isset($contents) || count($contents)<1)
        >
            Remove selected
        </button>
    </form>
</div>
