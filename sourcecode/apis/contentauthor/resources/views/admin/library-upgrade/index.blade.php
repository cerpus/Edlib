@extends('layouts.admin')
@php
    $activeTab = request()->query('activetab', 'tabContentTypes');
@endphp
@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Manage H5P content types</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel with-nav-tabs panel-default">
                    <div class="panel-heading">
                        <div class="panel-heading">
                            <ul class="nav nav-tabs">
                                <li @class(['active' => $activeTab === 'tabContentTypes'])>
                                    <a href="#tabContentTypes" data-toggle="tab">
                                        Installed content types
                                    </a>
                                </li>
                                <li @class(['active' => $activeTab === 'tabLibraries'])>
                                    <a href="#tabLibraries" data-toggle="tab">
                                        Installed libraries
                                    </a>
                                </li>
                                <li @class(['active' => $activeTab === 'tabUpload'])>
                                    <a href="#tabUpload" data-toggle="tab">
                                        Upload content type
                                    </a>
                                </li>
                                <li @class(['active' => $activeTab === 'tabInstall'])>
                                    <a href="#tabInstall" data-toggle="tab">
                                        Install from h5p.org
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div
                                id="tabContentTypes"
                                @class([
                                    "tab-pane fade",
                                    'in active' => $activeTab === 'tabContentTypes',
                                ])
                            >
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4>Local cache of content types available from h5p.org</h4>
                                    </div>
                                    @include('admin.library-upgrade.update-content-type-cache', ['activeTab' => 'tabContentTypes'])
                                </div>
                                <p>
                                    The list contains content types that are currently installed in Edlib.
                                    If available new versions can be downloaded and installed.
                                </p>
                                <p>
                                    @include('admin.fragments.update-explanation')
                                </p>
                                <p>
                                    Installed H5P Core version: {{ join('.', H5PCore::$coreApi) }}
                                </p>
                                <div class="panel-body row">
                                    @include('admin.fragments.library-table', [
                                        'libraries' => $installedContentTypes,
                                        'showCount' => true,
                                        'activetab' => 'tabContentTypes'
                                    ])
                                </div>
                            </div>
                            <div
                                @class([
                                    "tab-pane fade",
                                    'in active' => $activeTab === 'tabLibraries',
                                ])
                                id="tabLibraries"
                            >
                                <p>Libraries are used by content types, and are installed/updated when content types are installed/updated.</p>
                                <div class="panel-body row">
                                    @include('admin.fragments.library-table', [
                                        'libraries' => $installedLibraries,
                                        'showCount' => true,
                                        'activetab' => 'tabLibraries'
                                    ])
                                </div>
                            </div>
                            <div
                                id="tabUpload"
                                @class([
                                    "tab-pane fade",
                                    'in active' => $activeTab === 'tabUpload',
                                ])
                            >
                                <h4>Install or update H5P content types and libraries by uploading a file in <code>.h5p</code> format.</h4>
                                <p>Any content in the file will not be imported, only content types and libraries.</p>
                                <p>
                                    Content type and libraries must be compatible with H5P Core version {{ join('.', H5PCore::$coreApi) }}
                                </p>
                                <p>
                                    @include('admin.fragments.update-explanation')
                                </p>
                                @include('admin.library-upgrade.upload-content-type', [
                                    'activetab' => 'tabUpload',
                                ])
                            </div>
                            <div
                                id="tabInstall"
                                @class([
                                    "tab-pane fade",
                                    'in active' => $activeTab === 'tabInstall',
                                ])
                            >
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4>Local cache of content types available from h5p.org</h4>
                                    </div>
                                    @include('admin.library-upgrade.update-content-type-cache', ['activeTab' => 'tabInstall'])
                                </div>
                                <h4>Content types available from <a href="https://h5p.org" target="_blank">h5p.org</a></h4>
                                <p>
                                    The list contains content types that was available when the local cache was updated. Available content types are maintained by H5P Group.
                                </p>
                                <p>
                                    @include('admin.fragments.update-explanation')
                                </p>
                                <p>
                                    Installed H5P Core version: {{ join('.', H5PCore::$coreApi) }}
                                </p>
                                <div class="panel-body row">
                                    @include('admin.fragments.library-table', [
                                        'libraries' => $available,
                                        'showSummary' => true,
                                        'activetab' => 'tabInstall'
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
