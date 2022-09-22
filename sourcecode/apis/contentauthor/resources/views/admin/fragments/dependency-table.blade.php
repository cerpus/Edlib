<div class="panel-body row">
    <table class="table table-striped">
        <tr>
            <th>Library id</th>
            <th>Machine name</th>
            <th>Required version</th>
            <th>Installed version</th>
            <th>Set in DB</th>
        </tr>
        @foreach($dependencies as $dep)
            <tr>
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
                <td>{{ $dep['dependencySet'] ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
        @foreach($extraDependencies as $dep)
            <tr>
                <td>{{ $dep->id }}</td>
                <td>
                    <a href="{{ route('admin.check-library', [$dep->id]) }}">{{ $dep->name }}</a>
                </td>
                <td></td>
                <td>{{ $dep->major_version . '.' . $dep->minor_version . '.' . $dep->patch_version }}</td>
                <td>Yes</td>
            </tr>
        @endforeach
    </table>
</div>
