@extends('layout')

@section('title', 'Preview')

@section('content')
    <x-lti-launch
        :launchUrl="$content->latestVersion->resource->view_launch_url"
        :ltiVersion="$content->latestVersion->resource->tool->lti_version"
        :preview="true"
    />
@endsection
