@extends('layouts.admin')


@section('content')
    <div class="container">
        @if(session()->has('message'))
            <div class="row">
                <div class="alert alert-info">{{ session('message') }}</div>
            </div>
        @endif

        <div class="panel panel-default">
            <div class="panel panel-default">
                <div class="panel panel-default">
                    <div class="panel-heading">System Info</div>
                    <div class="panel-body">
                        <p><b>Laravel version</b>: {{ app()->version() }}</p>
                        <hr>
                        <p><b>Load averages</b>: {{ $loadAvg[0] }}, {{ $loadAvg[1] }}, {{ $loadAvg[2] }}</p>
                        <p><b>Memory usage</b>: {{ sprintf('%.2f', $memoryUsage) }}% </p>
                        <p><b>Avail mem</b>: {{ sprintf('%.2f', $availMem) }} MB</p>
                        <p><b>Uptime</b>: {{ $uptime }}</p>
                        <hr>
                        <p><b>PHP version</b>: {{ $phpVersion }}</p>
                        <p><b>PHP Memory Limit</b>: {{ $memoryLimit }}</p>
                        <hr>
                        <p><b>CPU model</b>: {{ $cpuInfo['cpuModel'] }}</p>
                        <p><b>Physical CPU cores</b>: {{ $cpuInfo['physicalCores'] }}</p>
                        <p><b>CPU cores (logical)</b>: {{ $cpuInfo['logicalCores'] }}</p>
                        <p><b>BOGOMips (per core)</b>: {{ $cpuInfo['bogoMips'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">Queue handling</div>
            <div class="panel-body">
                <a href="/admin/horizon">Go to Horizon Queue manager</a>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">ENV variables</div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <th scope="col">Key</th>
                    <th scope="col">Value</th>
                    </thead>
                    <tbody>
                    @foreach($env as $key => $value)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>
                                @if(str_contains($value, "<a href"))
                                    {!! $value !!}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">PHP Extensions</div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <th scope="col">Name</th>
                    </thead>
                    <tbody>
                    @foreach($extensions as $extension)
                        <tr>
                            <td>
                                    {{ $extension }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
