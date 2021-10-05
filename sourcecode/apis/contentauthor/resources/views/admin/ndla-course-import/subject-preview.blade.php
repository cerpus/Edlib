@extends('layouts.admin')


@section('content')
    @if(resolve(\App\Libraries\H5P\Interfaces\H5PAdapterInterface::class)->adapterIs('cerpus'))
        <div class="container">
            @if(session()->has('message'))
                <div class="row">
                    <div class="alert alert-info">{!! session('message')  !!}</div>
                </div>
            @endif

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <span class="h4">Subject Preview</span>
                    <h1>{{ $subject->title }}</h1>
                    <p class="lead">{{ $subject->intro }}</p>
                    <h3>Courses</h3>
                    <table class="table table-striped table-hover">
                        <tr>
                            <th>Course</th>
                        </tr>
                        @forelse ($subject->courses as $course)
                            @include('admin.ndla-course-import.fragments.course-line')
                            <tr>
                                <th>
                                    Course
                                </th>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2"><h3>No courses detected</h3></td>
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
