@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3>H5P content info</h3>
                    </div>
                    <div class="panel-body">
                        <div>
                            <h4>Value types</h4>
                            <ul>
                                <li><b>Content</b> - When editing this is displayed under Properties</li>
                                <li><b>Resource/Folium</b> - Displayed in the preview popup and in embed links, gets the latest <i>published</i> version</li>
                                <li><b>Usage</b> - The id in the LTI launch link</li>
                                <li><b>Version</b> - Internal version id</li>
                            </ul>
                        </div>
                        <form>
                            <div class="form-group">
                                <label for="value_type">Value type</label>
                                <br>
                                <select name="valueType" id="value_type">
                                    <option value="content" {{$valueType === 'content' ? 'selected' : '' }}>Content Id (integer)</option>
                                    <option value="resource" {{$valueType === 'resource' ? 'selected' : '' }}>Resource/Folium Id (uuid)</option>
                                    <option value="usage" {{$valueType === 'usage' ? 'selected' : '' }}>Usage Id (uuid)</option>
                                    <option value="version" {{$valueType === 'version' ? 'selected' : '' }}>Version Id (uuid)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="value">Value</label>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="value"
                                    id="value"
                                    value="{{$value ?? ""}}"
                                >
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        @isset($error)
                            <div style="margin-top:2em;">
                                {{ $error }}
                            </div>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
