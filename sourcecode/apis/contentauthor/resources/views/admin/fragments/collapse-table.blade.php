<table class="table">
    <thead>
        <tr>
            <th></th>
            <th>
                Machine name
                @if(($activetab !== 'tabInstall' && !$sortColumn) || $sortColumn === 'machinename')
                    <i class="fa fa-sort-desc"></i>
                @else
                    <a href="{{ url()->query(url()->full(), ['sort' => 'machinename', 'activetab' => $activetab]) }}">
                        <i class="fa fa-sort"></i>
                    </a>
                @endif
            </th>
            <th>
                Title
                @if($sortColumn === 'title')
                    <i class="fa fa-sort-asc"></i>
                @else
                    <a href={{ url()->query(url()->full(), ['sort' => 'title', 'activetab' => $activetab]) }}>
                        <i class="fa fa-sort"></i>
                    </a>
                @endif
            </th>
            <th>Version</th>
            @if($isContentType)
                <th>Hub version</th>
            @endif
            @isset($showCount)
                <th>
                    Created
                    @if($sortColumn === 'created')
                        <i class="fa fa-sort-asc"></i>
                    @else
                        <a href={{ url()->query(url()->full(), ['sort' => 'created', 'activetab' => $activetab]) }}>
                            <i class="fa fa-sort"></i>
                        </a>
                    @endif
                </th>
                <th>
                    Updated
                    @if($sortColumn === 'updated')
                        <i class="fa fa-sort-asc"></i>
                    @else
                        <a href={{ url()->query(url()->full(), ['sort' => 'updated', 'activetab' => $activetab]) }}>
                            <i class="fa fa-sort"></i>
                        </a>
                    @endif
                </th>
                <th><abbr title="Number of content using it as main content type">#contents</abbr></th>
            @endif
            @isset($showSummary)
                <th>Summary</th>
            @endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse ( $libraries as $contentType)
        @php
            $collapseId = "lib-" . $contentType[0]['libraryId'];
            $libCount = count($contentType)
        @endphp
        @foreach($contentType as $library)
            @if ($libCount > 1)
                @if ($loop->first)
                    <tr
                        @class([
                            'row-odd' => $loop->parent->odd,
                        ])
                    >
                        <td>
                            <button
                                type="button"
                                data-toggle="collapse"
                                data-target="[data-collapse-id='{{$collapseId}}']"
                                class="btn btn-xs collapse-library btn-link h5p-action-button fa fa-plus"
                                title="Show older versions ({{$loop->remaining}})"
                            ></button>
                        </td>
                        @include('admin.fragments.library-row')
                    </tr>
                @else
                    <tr
                        data-collapse-id="{{$collapseId}}"
                        @class([
                            'collapse',
                            'collapse-item',
                            'collapse-item-first' => $loop->iteration == 2,
                            'collapse-item-last' => $loop->last,
                        ])
                    >
                        <td></td>
                        @include('admin.fragments.library-row')
                    </tr>
                @endif
            @else
                <tr @if($loop->parent->odd)class="row-odd"@endif>
                    <td></td>
                    @include('admin.fragments.library-row')
                </tr>
            @endif
        @endforeach
    @empty
        <td colspan="5"><p>No content</p></td>
    @endforelse
    </tbody>
</table>
