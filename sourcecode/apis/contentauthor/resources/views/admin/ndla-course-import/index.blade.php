@extends('layouts.admin')


@section('content')
    @if(resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->adapterIs('cerpus'))
        <div class="container">
            @if(session()->has('message'))
                <div class="row">
                    <div class="alert alert-info">{{ session('message') }}</div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <h1>Course import</h1>
                    <table class="table table-striped table-hover">
                        <tr>
                            <th>Course</th>
                        </tr>
                        @forelse($subjects as $subject)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.courseimport.subject-preview', $subject->id) }}">{{ $subject->name }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No subjects</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    @else
        <h1>This is not the environment i expected!</h1>
    @endif
@endsection
