<table class="table table-striped">
    <tr>
        <th>Machine name</th>
        <th>Title</th>
        <th>Installed</th>
        <th>Hub version</th>
        <th>Summary</th>
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
            <td class="text-center">{{ $library['version'] }}</td>
            <td class="text-center">{{ $library['hubVersion'] }}</td>
            <td>{{ $library['summary'] ?: '' }}
            <td
                class="h5p-action-button-container"
                data-library-name="{{$library['machineName']}}"
                data-library-major="{{$library['majorVersion']}}"
                data-library-minor="{{$library['minorVersion']}}"
            >
                @if ($library['hubUpgrade'] !== null)
                    <button
                        type="button"
                        @class([
                            'btn btn-xs install-btn h5p-action-button',
                            'btn-primary' => $library['hubUpgradeIsPatch'] === null,
                            'btn-success' => $library['hubUpgradeIsPatch'] === false,
                            'btn-warning' => $library['hubUpgradeIsPatch'] === true,
                            'btn-danger' => $library['hubUpgradeError'] !== null,
                        ])
                        data-name="{{ $library['machineName'] }}"
                        data-ajax-url="{{ route('admin.ajax') }}"
                        data-ajax-action="{{ H5PEditorEndpoints::LIBRARY_INSTALL }}"
                        data-error-message="{{ $library['hubUpgradeError'] }}"
                        @isset($activetab)
                            data-ajax-activetab="{{$activetab}}"
                        @endisset
                        title="{{ $library['hubUpgradeError'] ?? $library['hubUpgradeMessage'] }}"
                    >
                        <span class="fa fa-cloud-download"></span>
                    </button>
                @else
                    <div class="h5p-action-button"></div>
                @endif
                @if(!empty($library['external_link']))
                    <a
                        class="btn btn-default btn-xs"
                        target="_blank"
                        href="{{ $library['external_link'] }}"
                        title="View on H5P.org"
                    >
                        <span class="fa fa-external-link"></span>
                    </a>
                @endif
            </td>
        </tr>
    @empty
        <td colspan="5"><p>No content</p></td>
    @endforelse
</table>
