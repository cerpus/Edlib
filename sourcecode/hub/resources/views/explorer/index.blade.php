@extends('layout')

@section('title', trans('My content!'))

@section('content')
    <div class="grid">
        @foreach ($contents as $content)
            <x-content-card :content="$content" />
        @endforeach
    </div>

    {{ $contents->links() }}
@endsection
