@extends('layouts.admin')

@section('content')
    <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading pan">Update Libraries</div>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Enabled</th>
                    <th>Score</th>
                    <th>Name</th>
                    <th><a href="{{ route('admin.capability.refresh') }}" class="btn btn-primary pull-right">Refresh</a></th>
                </tr>
                </thead>
                <tbody>
                @foreach($capabilities as $capability)
                    <tr>
                        <td>
                            <input id="enable{{ $capability->id }}" type="checkbox" name="enabled" value="1"
                                   data-endpoint="{{ route('admin.capability.enabled', $capability->id) }}"
                                   data-method="POST"
                                   onchange="enableForm('{{ $capability->id }}')"
                                    {{ !empty($capability->enabled)?'checked':'' }}>
                        </td>
                        <td>
                            <input id="score{{ $capability->id }}" type="checkbox" name="score" value="1"
                                   data-endpoint="{{ route('admin.capability.score', $capability->id) }}"
                                   data-method="POST"
                                   onchange="scoreForm('{{ $capability->id }}')"
                                    {{ !empty($capability->score)?'checked':'' }}>
                        </td>
                        <td>{{ $capability->name }}</td>
                        <td>
                            <form id="descriptionForm{{ $capability->id }}"
                                  action="{{ route('admin.capability.description', $capability->id) }}"
                                  method="POST"
                            >
                                Title: <input id="title{{ $capability->id }}" type="text" name="title"
                                              value="{{ $capability->title }}"
                                              onchange="descriptionForm('{{ $capability->id }}')">
                                Description: <input id="description{{ $capability->id }}" type="text" name="description"
                                                    value="{{ $capability->description }}"
                                                    onchange="descriptionForm('{{ $capability->id }}')">
                                <select id="locale{{ $capability->id }}" name="'locale"
                                        data-endpoint="{{ route('admin.capability.translation', $capability->id) }}"
                                        onchange="getTranslation('{{ $capability->id }}')">
                                    <option value="en-gb" {{ $locale == 'en-gb'?'selected':'' }}>English</option>
                                    <option value="nb-no"{{ $locale == 'nb-no'?'selected':'' }}>Norsk(Bokmål)</option>
                                </select>

                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <script>
        function enableForm(capabilityNo) {
            var enabledInput = document.getElementById('enable' + capabilityNo);
            var enabled = enabledInput.checked ? '1' : '0';
            var action = enabledInput.dataset.endpoint;
            var method = enabledInput.dataset.method;
            $.ajax({
                url: action,
                method: method,
                data: {
                    enabled: enabled
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
                var en = document.getElementById('enable' + data.id);
                en.checked = data.enabled == 1 ? true : false;
            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }

        function scoreForm(capabilityNo) {
            var scoreInput = document.getElementById('score' + capabilityNo);
            var score = scoreInput.checked ? '1' : '0';
            var action = scoreInput.dataset.endpoint;
            var method = scoreInput.dataset.method;
            $.ajax({
                url: action,
                method: method,
                data: {
                    score: score
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
                var en = document.getElementById('score' + data.id);
                en.checked = data.score == 1 ? true : false;
            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }

        function descriptionForm(capabilityNo) {
            var title = document.getElementById('title' + capabilityNo).value;
            var description = document.getElementById('description' + capabilityNo).value;
            var locale = document.getElementById('locale' + capabilityNo).value;

            var form = document.getElementById('descriptionForm' + capabilityNo);
            var action = form.action;
            var method = form.method;

            $.ajax({
                url: action,
                method: method,
                data: {
                    title: title,
                    description: description,
                    locale: locale
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }

        function getTranslation(capabilityNo) {
            var localeSelect = document.getElementById('locale' + capabilityNo);
            var locale = localeSelect.value;
            var action = localeSelect.dataset.endpoint;
            var method = 'GET';

            $.ajax({
                url: action,
                method: method,
                data: {
                    locale: locale
                },
                dataType: 'json'
            }).done(function (data, status, xhr) {
                var id = data.capability_id;
                document.getElementById('title' + id).value = data.title;
                document.getElementById('description' + id).value = data.description;


            }).fail(function (data, status, error) {
                console.log(data + ' ' + status);
            });
        }

    </script>

@endsection




