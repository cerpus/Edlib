<table class="table">
    <tr>
        <th>Title</th>
        <th>#contents</th>
        <th>#libdependencies</th>
        <th>Actions</th>
    </tr>
    @forelse ( $libraries as $library)
        <tr>
            <td>{{ $library['title'] }}</td>
            <td>{{ $library['numContent'] ?: '' }}</td>
            <td>{{ $library['numLibraryDependencies'] ?: '' }}</td>
            <td
                data-library-name="{{$library['machineName']}}"
                data-library-major="{{$library['majorVersion']}}"
                data-library-minor="{{$library['minorVersion']}}"
            >
                @if (!empty($library['upgradeUrl']))
                    <a title="Upgrade" href="{{ $library['upgradeUrl'] }}">
                        <button
                                type="button"
                                class="btn btn-info btn-xs"
                                title="Upgrade"
                        >
                            <span class="fa fa-refresh" />
                        </button>
                    </a>
                @elseif ($library['hubUpgrade'] !== null)
                    <button
                            type="button"
                            class="btn btn-success btn-xs install-btn"
                            data-name="{{$library['machineName']}}"
                            data-ajax-url="{{route('admin.ajax')}}"
                            data-ajax-action="{{H5PEditorEndpoints::LIBRARY_INSTALL}}"
                            title="Install version {{ $library['hubUpgrade'] }}"
                    >
                        <span class="fa fa-cloud-download" />
                    </button>
                @endif
                @if(!empty($library['libraryId']))
                    <button
                            type="button"
                            class="btn btn-warning btn-xs rebuild-btn"
                            data-libraryId="{{$library['libraryId']}}"
                            data-ajax-url="{{route('admin.ajax')}}"
                            data-ajax-action="{{\App\Libraries\H5P\AjaxRequest::LIBRARY_REBUILD}}"
                            title="Rebuild"
                    >
                        <span class="fa fa-history" />
                    </button>
                @endif
                @if(empty($library['numLibraryDependencies']) && !empty($library['libraryId']))
                    <button
                            type="button"
                            class="btn btn-danger btn-xs delete-btn"
                            data-libraryId="{{$library['libraryId'] ?? null}}"
                            data-ajax-url="{{route('admin.ajax')}}"
                            data-ajax-action="{{\App\Libraries\H5P\AjaxRequest::LIBRARY_DELETE}}"
                            title="Delete"
                    >
                         <span class="fa fa-trash" />
                    </button>
                @endif
            </td>
        </tr>
    @empty
        <td colspan="4"><p>No old content</p></td>
    @endforelse
</table>
