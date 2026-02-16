<td>
    @if (!empty($library['libraryId']))
        <a href="{{ route('admin.check-library', [$library['libraryId']]) }}">{{ $library['machineName'] }}</a>
    @else
        {{ $library['machineName'] }}
    @endif
</td>
<td>{{ $library['title'] }}</td>
<td>{{ $library['version'] }}</td>
@if($isContentType)
    <td>{{ $library['hubVersion'] ?? '' }}</td>
@endif
@isset($showCount)
    <td>{{ \Carbon\Carbon::createFromTimestamp($library['createdTs'])->format('Y-m-d H:i:s e') }}</td>
    <td>{{ \Carbon\Carbon::createFromTimestamp($library['updatedTs'])->format('Y-m-d H:i:s e') }}</td>
    <td>
        @if (!empty($library['numContent']))
            <a href="{{ route('admin.content-library', [$library['libraryId']]) }}">{{ $library['numContent'] }}</a>
        @endif
    </td>
@endif
@isset($showSummary)
    <td>{{ $library['coreVersion'] }}</td>
    <td>{{ \Carbon\Carbon::createFromTimestamp($library['updatedTs'])->format('Y-m-d H:i:s e') }}</td>
    <td>{{ $library['createdBy'] ?: '' }}
    <td>{{ $library['summary'] ?: '' }}
@endif
<td
    class="h5p-action-button-container"
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
    @if(!empty($library['libraryId']) && isset($library['canRebuild']) && $library['canRebuild'] === true)
        <button
            type="button"
            class="btn btn-default btn-xs rebuild-btn h5p-action-button"
            data-libraryId="{{$library['libraryId']}}"
            data-ajax-url="{{route('admin.ajax')}}"
            data-ajax-action="{{\App\Libraries\H5P\AjaxRequest::LIBRARY_REBUILD}}"
            @isset($activetab)
                data-ajax-activetab="{{$activetab}}"
            @endisset
            title="Rebuild"
        >
            <span class="fa fa-history"></span>
        </button>
    @endif
    @if(array_key_exists('canDelete', $library) && $library['canDelete'] === true)
        <button
            type="button"
            class="btn btn-danger btn-xs h5p-action-button delete-btn"
            data-library="{{ $library['machineName'] . ': ' . $library['title'] }}"
            data-ajax-url="{{ route('admin.delete-library', [$library['libraryId']]) }}"
            @isset($activetab)
                data-ajax-activetab="{{$activetab}}"
            @endisset
            title="Delete"
        >
            <span class="fa fa-trash"></span>
        </button>
    @elseif(isset($showCount))
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
