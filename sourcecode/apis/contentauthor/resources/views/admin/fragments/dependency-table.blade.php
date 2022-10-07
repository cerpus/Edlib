<div class="panel-body row">
    <table class="table table-striped">
        <tr>
            <th>Library id</th>
            <th>Machine name</th>
            <th>Required version</th>
            <th>DB version</th>
            <th>Set in DB</th>
        </tr>
        @foreach($dependencies as $dep)
            @if (empty($dep['library']) || !$dep['dependencySet'])
                <tr style="background-color: #f2dede">
            @else
                <tr>
            @endif
                <td>{{ $dep['library']->id ?? ''}}</td>
                <td>
                    @isset($dep['library'])
                        <a href="{{ route('admin.check-library', [$dep['library']->id]) }}">{{ $dep['machineName'] }}</a>
                    @else
                        {{ $dep['machineName'] }}
                    @endif
                </td>
                <td>{{ $dep['majorVersion'] . '.' . $dep['minorVersion'] }}</td>
                <td>{{ $dep['library'] ? $dep['library']->major_version . '.' . $dep['library']->minor_version . '.' . $dep['library']->patch_version : '' }}</td>
                <td>{{ $dep['library'] ? $dep['dependencySet'] ? 'Yes' : 'No' : ''}}</td>
            </tr>
        @endforeach
        @foreach($extraDependencies as $dep)
            <tr style="background-color: #f2dede">
                <td>{{ $dep->requiredLibrary->id }}</td>
                <td>
                    <a href="{{ route('admin.check-library', [$dep->requiredLibrary->id]) }}">{{ $dep->requiredLibrary->name }}</a>
                </td>
                <td></td>
                <td>{{ $dep->requiredLibrary->major_version . '.' . $dep->requiredLibrary->minor_version . '.' . $dep->requiredLibrary->patch_version }}</td>
                <td>Yes</td>
            </tr>
        @endforeach
    </table>
</div>
