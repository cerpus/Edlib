@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Content version info (and more)</div>
                    <div class="panel-body">
                        <form>
                            <div class="form-group">
                                <label for="contentId">Content Id:</label>
                                <input class="form-control" type="text" name="contentId" value="{{$contentId ?? ""}}" id="contentId" placeholder="Paste or type in the ID of the content">
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        <div class="versioncontent">
                            @if(!empty($versionData))
                                <pre>@json($versionData, JSON_PRETTY_PRINT)</pre>
                            @else
                                @if($isContentVersioned)
                                    <p>No data</p>
                                @else
                                    <p class="alert alert-info">The content is not versioned</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
@endpush
