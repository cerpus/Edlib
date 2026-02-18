<table class="table table-striped">
    <thead>
    <tr>
        <th>
            Machine name
            @if(($activetab !== 'tabInstall' && !$sortColumn) || $sortColumn === 'machinename')
                <i class="vertical-align-middle fa fa-sort-desc"></i>
            @else
                <a href="{{ url()->query(url()->full(), ['sort' => 'machinename', 'activetab' => $activetab]) }}">
                    <i class="vertical-align-middle fa fa-sort"></i>
                </a>
            @endif
        </th>
        <th>
            Title
            @if($sortColumn === 'title')
                <i class="vertical-align-middle fa fa-sort-desc"></i>
            @else
                <a href={{ url()->query(url()->full(), ['sort' => 'title', 'activetab' => $activetab]) }}>
                    <i class="vertical-align-middle fa fa-sort"></i>
                </a>
            @endif
        </th>
        <th>Installed version</th>
        @if($isContentType)
            <th>Hub version</th>
        @endif
        @isset($showCount)
            <th>
                Installed
                @if($sortColumn === 'created')
                    <i class="vertical-align-middle fa fa-sort-desc"></i>
                @else
                    <a href={{ url()->query(url()->full(), ['sort' => 'created', 'activetab' => $activetab]) }}>
                        <i class="vertical-align-middle fa fa-sort"></i>
                    </a>
                @endif
            </th>
            <th>
                Updated
                @if($sortColumn === 'updated')
                    <i class="vertical-align-middle fa fa-sort-desc"></i>
                @else
                    <a href={{ url()->query(url()->full(), ['sort' => 'updated', 'activetab' => $activetab]) }}>
                        <i class="vertical-align-middle fa fa-sort"></i>
                    </a>
                @endif
            </th>
            <th><abbr title="Number of content using it as main content type">#contents</abbr></th>
        @endif
        @isset($showSummary)
            <th>Core version</th>
            <th>
                Updated
                @if($activetab === 'tabInstall' && (!$sortColumn || $sortColumn === 'updated'))
                    <i class="vertical-align-middle fa fa-sort-desc"></i>
                @else
                    <a href={{ url()->query(url()->full(), ['sort' => 'updated', 'activetab' => $activetab]) }}>
                        <i class="vertical-align-middle fa fa-sort"></i>
                    </a>
                @endif
            </th>
            <th>Created by</th>
            <th>Summary</th>
        @endif
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ( $libraries as $library)
        <tr>
            @include('admin.fragments.library-row')
        </tr>
    @empty
        <td colspan="5"><p>No content</p></td>
    @endforelse
    </tbody>
</table>
