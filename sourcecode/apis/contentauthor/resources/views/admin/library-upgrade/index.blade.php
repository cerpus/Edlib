@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div id="minor-publishing" class="panel panel-default">
                            <div class="panel-heading">
                                <h3>Install or update H5P content types and libraries</h3>
                            </div>

                            <div class="panel-body row">
                                <form method="post" enctype="multipart/form-data" id="h5p-library-form" class="col-md-6">
                                    <h4>By file upload</h4>
                                    <p>File must be .h5p format.</p>
                                    <input type="file" name="h5p_file" id="h5p-file"/>
                                    <br>
                                    <div class="h5p-disable-file-check">
                                        <label><input type="checkbox" name="h5p_upgrade_only" id="h5p-upgrade-only"/> Only update existing libraries</label>
                                        <br>
                                        <label><input type="checkbox" name="h5p_disable_file_check" id="h5p-disable-file-check"/> Disable file extension check</label>
                                    </div>
                                    <br>
                                    <input type="hidden" id="lets_upgrade_that" name="lets_upgrade_that" value="228e7591a1">
                                    <input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/admin.php?page=h5p_libraries">
                                    {!! csrf_field() !!}
                                    <div id="major-publishing-actions" class="submitbox">
                                        <input type="submit" name="submit" value="Upload" class="button button-primary button-large btn btn-primary"/>
                                    </div>
                                </form>

                                <form action="{{ route('admin.check-for-updates') }}" method="post">
                                    @csrf
                                    <h4>Content types from h5p.org</h4>
                                    <button type="submit" class="btn btn-success">Check for updates</button>
                                </form>
                            </div>

                            @include('fragments.invalidFlashMessage')
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3>Content types installed</h3>
                            </div>

                            <div class="panel-body row">
                                @include('admin.fragments.library-table', [
                                    'libraries' => $installedContentTypes,
                                    'showCount' => true,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3>Libraries installed</h3>
                            </div>

                            <div class="panel-body row">
                                @include('admin.fragments.library-table', [
                                    'libraries' => $installedLibraries,
                                    'showCount' => true,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3>Install from H5P.org</h3>
                            </div>

                            <div class="panel-body row">
                                @include('admin.fragments.library-table', [
                                    'libraries' => $available,
                                    'showSummary' => true,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
