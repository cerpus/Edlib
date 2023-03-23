@extends('layout')

@section('title', sprintf('Create a thing with %s', $tool->name))

@section('content')
    <x-lti-launch
        :launchUrl="$tool->creator_launch_url"
        :ltiVersion="$tool->lti_version"
    />
@endsection()
