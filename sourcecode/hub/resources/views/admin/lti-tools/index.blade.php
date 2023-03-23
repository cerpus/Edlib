@extends('layout')

@section('title', 'LTI tools')

@section('content')
    <ul>
        @foreach ($tools as $tool)
            <li>
                <strong>{{ $tool->name }}</strong>
                <dl>
                    <dt>Consumer ID
                    <dd>
                        @if ($tool->consumer_id)
                            <kbd>{{ $tool->consumer_id }}</kbd>
                        @else
                            (none)
                        @endif
                    <dt>Associated resources
                    <dd>{{ $tool->resources_count }}
                </dl>
            </li>
        @endforeach
    </ul>

    <p><a href="{{ route('admin.lti-tools.add') }}">Add LTI tool</a></p>
@endsection
