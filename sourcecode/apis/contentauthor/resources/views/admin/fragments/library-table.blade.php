<table class="table table-striped">
    <tr>
        <th>Machine name</th>
        <th>Title</th>
        @isset($showCount)
            <th>#contents</th>
            <th>#libdependencies</th>
        @endif
        @isset($showSummary)
            <th>Summary</th>
        @endif
        <th>Actions</th>
    </tr>
    @forelse ( $libraries as $library)
        <tr>
            <td>
                @if (!empty($library['libraryId']))
                    <a href="{{ route('admin.check-library', [$library['libraryId']]) }}">{{ $library['machineName'] }}</a>
                @else
                    {{ $library['machineName'] }}
                @endif
            </td>
            <td>{{ $library['title'] }}</td>
            @isset($showCount)
                <td>
                    @if (!empty($library['numContent']))
                        <a href="{{ route('admin.content-library', [$library['libraryId']]) }}">{{ $library['numContent'] }}</a>
                    @endif
                </td>
                <td>{{ $library['numLibraryDependencies'] ?: '' }}</td>
            @endif
            @isset($showSummary)
                <td>{{ $library['summary'] ?: '' }}
            @endif
            <td
                data-library-name="{{$library['machineName']}}"
                data-library-major="{{$library['majorVersion']}}"
                data-library-minor="{{$library['minorVersion']}}"
            >
                @if (!empty($library['upgradeUrl']))
                    <a title="Content bulk upgrade" href="{{ $library['upgradeUrl'] }}">
                        <button
                                type="button"
                                class="btn btn-info btn-xs h5p-action-button"
                                title="Content bulk upgrade"
                        >
                            <span class="fa fa-refresh"></span>
                        </button>
                    </a>
                @elseif ($library['hubUpgrade'] !== null)
                    <button
                            type="button"
                            class="btn btn-success btn-xs install-btn h5p-action-button"
                            data-name="{{$library['machineName']}}"
                            data-ajax-url="{{route('admin.ajax')}}"
                            data-ajax-action="{{H5PEditorEndpoints::LIBRARY_INSTALL}}"
                            title="Install version {{ $library['hubUpgrade'] }}"
                    >
                        <span class="fa fa-cloud-download"></span>
                    </button>
                @else
                    <div class="h5p-action-button"></div>
                @endif
                @if(!empty($library['libraryId']))
                    <button
                            type="button"
                            class="btn btn-warning btn-xs rebuild-btn h5p-action-button"
                            data-libraryId="{{$library['libraryId']}}"
                            data-ajax-url="{{route('admin.ajax')}}"
                            data-ajax-action="{{\App\Libraries\H5P\AjaxRequest::LIBRARY_REBUILD}}"
                            title="Rebuild"
                    >
                        <span class="fa fa-history"></span>
                    </button>
                @endif
                @if(empty($library['numLibraryDependencies']) && !empty($library['libraryId']))
                    <button
                            type="button"
                            class="btn btn-danger btn-xs delete-btn"
                            data-ajax-url="{{ route('admin.delete-library', [$library['libraryId']]) }}"
                            title="Delete"
                    >
                        <span class="fa fa-trash"></span>
                    </button>
                @endif
                @if(!empty($library['external_link']))
                    <a
                        class="btn btn-warning btn-xs"
                        target="_blank"
                        href="{{ $library['external_link'] }}"
                        title="View on H5P.org"
                    >
                        <span class="fa fa-external-link-square"></span>
                    </a>
                @endif
            </td>
        </tr>
    @empty
        <td colspan="5"><p>No content</p></td>
    @endforelse
</table>
