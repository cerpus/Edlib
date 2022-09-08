@extends('layouts.admin')

@section('content')
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading pan">
                <h3>Library capabilities ({{ count($libraries) }} libraries)</h3>
            </div>
            <a href="{{ route('admin.capability.refresh') }}" class="btn btn-primary pull-right">Refresh</a>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Enabled</th>
                        <th>Name</th>
                        <th>Score</th>
                        <th>presave.js</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($libraries as $library)
                    <tr>
                        <td>
                            <input id="enable{{ $library->capability->id }}" type="checkbox" name="enabled" value="1"
                                   data-endpoint="{{ route('admin.capability.enabled', $library->capability->id) }}"
                                   data-method="POST"
                                   onchange="enableForm('{{ $library->capability->id }}')"
                                {{ !empty($library->capability->enabled)?'checked':'' }}>
                        </td>
                        <td>
                            {{
                                $library->name . ' ' .
                                $library->major_version . '.' .
                                $library->minor_version . '.' .
                                $library->patch_version
                            }}
                        </td>
                        <td>
                            <input id="score{{ $library->capability->id }}" type="checkbox" name="score" value="1"
                                   data-endpoint="{{ route('admin.capability.score', $library->capability->id) }}"
                                   data-method="POST"
                                   onchange="scoreForm('{{ $library->capability->id }}')"
                                {{ !empty($library->capability->score)?'checked':'' }}>
                        </td>
                        <td>
                            @if($library->presaveInstalled)
                                Installed
                            @elseif($library->presaveAvailable)
                                Available
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function enableForm(capabilityNo) {
            const enabledInput = document.getElementById('enable' + capabilityNo);
            const enabled = enabledInput.checked ? '1' : '0';
            const action = enabledInput.dataset.endpoint;
            const method = enabledInput.dataset.method;

            $.ajax({
                url: action,
                method: method,
                data: {
                    enabled: enabled
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
                const en = document.getElementById('enable' + data.id);
                en.checked = data.enabled == 1 ? true : false;
            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }

        function scoreForm(capabilityNo) {
            const scoreInput = document.getElementById('score' + capabilityNo);
            const score = scoreInput.checked ? '1' : '0';
            const action = scoreInput.dataset.endpoint;
            const method = scoreInput.dataset.method;

            $.ajax({
                url: action,
                method: method,
                data: {
                    score: score
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
                const en = document.getElementById('score' + data.id);
                en.checked = data.score == 1 ? true : false;
            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }
    </script>
@endsection
