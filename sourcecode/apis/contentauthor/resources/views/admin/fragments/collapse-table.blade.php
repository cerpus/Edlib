<table class="table table-striped">
    <tr>
        <th></th>
        <th @class(['alert-info' => request()->has('sort') === false || request()->get('sort') === 'machineName'])>
            Machine name
            <a href="?sort=machineName&activetab={{$activetab}}">
                <span class="fa fa-sort-alpha-asc"></span>
            </a>
        </th>
        <th @class(['alert-info' => request()->get('sort') === 'title'])>
            Title
            <a href="?sort=title&activetab={{$activetab}}">
                <span class="fa fa-sort-alpha-asc"></span>
            </a>
        </th>
        <th @class(['alert-info' => request()->get('sort') === 'updated'])>
            Updated
            <a href="?sort=updated&activetab={{$activetab}}">
                <span class="fa fa-sort-amount-desc"></span>
            </a>
        </th>
        <th>Version</th>
        @if($isContentType)
            <th>Hub version</th>
        @endif
        <th><abbr title="Number of content using library as main content type">#contents</abbr></th>
        @if(!$isContentType)
            <th><abbr title="Number of other content types or libraries that are referencing it">#libdependencies</abbr></th>
        @endif
        <th>Actions</th>
    </tr>
    @forelse ($libraries as $idx => $group)
        @if($group->count() > 1)
            <tr>
                <td>
                    <a data-toggle="collapse" href="#{{$activetab}}_{{$idx}}">
                        <span class="fa fa-list"></span>
                    </a>
                </td>
                @include('admin.fragments.content-type-row', [
                    'library' => $group->shift(),
                ])
            </tr>
        @else
            <tr>
                <td></td>
                @include('admin.fragments.content-type-row', [
                    'library' => $group->shift(),
                ])
        @endif
        @if($group->count() > 0)
            <tbody
                id="{{$activetab}}_{{$idx}}"
                class="collapse"
                style="border-color:#b5b5b5; border-style:solid; border-width: 0 2px 2px 2px"
            >
                @foreach($group as $num => $library)
                <tr>
                    <td></td>
                    @include('admin.fragments.content-type-row', [
                        'library' => $library,
                        'index' => $idx,
                        'title' => false,
                        ])
                </tr>
                @endforeach
            </tbody>
        @endif
    @empty
        <td colspan="6"><p>No content</p></td>
    @endforelse
</table>
