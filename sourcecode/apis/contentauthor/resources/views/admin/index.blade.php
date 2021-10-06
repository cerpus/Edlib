@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Admin</div>
                    <div class="panel-body">
                        <h2 class="text-center">Active edit locks: {{ $editLockCount }}</h2>
                        <a class="col-md-4 well well-lg" href="{{ route('admin.capability') }}">
                            <i class="glyphicon glyphicon-edit"></i> Capabilities
                        </a>
                        <a class="col-md-4 well well-lg" href="{{ route('admin.update-libraries') }}">
                            <i class="glyphicon glyphicon-upload"></i> Update H5P Libraries
                        </a>
                        <a class="col-md-4 well well-lg" href="{{ route('admin.games') }}">
                            <i class="glyphicon glyphicon-upload"></i> Update games
                        </a>
                    </div>
                    <div class="panel-body">
                        <h2 class="text-center">NDLA Import</h2>
                        @if(resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->showArticleImportExportFunctionality())
                            <a class="col-md-6 well well-lg text-center" href="{{ route('admin.ndla.index') }}">
                                <i class="glyphicon glyphicon-edit"></i> Import Articles
                            </a>
                            <a class="col-md-6 well well-lg text-center" href="{{ route('admin.learningpath.index') }}">
                                <i class="glyphicon glyphicon-edit"></i> Import Learning Paths
                            </a>
                            <a class="col-md-6 well well-lg text-center" href="{{ route('admin.courseimport.index') }}">
                                <i class="glyphicon glyphicon-edit"></i> Export Courses to EdStep
                            </a>
                            <a class="col-md-6 well well-lg text-center" href="{{ route('admin.importexport.index') }}">
                                <i class="glyphicon glyphicon-edit"></i> Import / Export settings and management
                            </a>
                            <a class="col-md-6 well well-lg text-center" href="{{ route('admin.norgesfilm.index') }}">
                                <i class="glyphicon glyphicon-film"></i> Norgesvideo Admin
                            </a>
                        @endif
                        <a class="col-md-6 well well-lg text-center" href="{{ route('admin.metadata.index') }}">
                            <i class="glyphicon glyphicon-edit"></i> Import metadata
                        </a>
                    </div>

                </div>
            </div>
        </div>
@endsection
