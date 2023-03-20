@extends('layout')

@section('title', trans('My content!'))

@section('content')
    <ul>
        @foreach ($contents as $content)
            <li>
                <h1 lang="{{ $content->language_alpha3 }}">
                    <a href="">{{ $content->latest->resource->title }}</a>
                </h1>

                <p>Language: {{ $content->latest->resource->language_alpha3 }}</p>

                <h2>List of versions</h2>

                <ul>
                    @foreach ($content->versions as $version)
                        <li>
                            <dl>
                                <dt>Id</dt><dd>{{ $version->id }}</dd>
                                <dt>Title</dt><dd>{{ $version->resource->title }}</dd>
                                <dt>Parent</dt><dd>{{ $version->parent?->id ?? 'none' }}</dd>
                            </dl>
                        </li>
                    @endforeach
                </ul>

                <nav>
                    <button>{{ trans('preview') }}</button>
                </nav>
            </li>
        @endforeach
    </ul>
@endsection
