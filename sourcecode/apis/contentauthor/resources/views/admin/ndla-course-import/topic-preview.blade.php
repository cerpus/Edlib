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
                    <span class="h4">
                        Course preview<br>
                        <button data-toggle="modal" data-target="#importAllModal" class="btn btn-link disabled">
                            Import all articles in this course</button>
                    </span>
                    <span class="pull-right">
                        <a href="{{ route('admin.courseimport.export', [$subjectId, $topicId]) }}"
                           class="btn btn-default pull-right">Export</a>
                    </span>
                    <h1>{{ $course->title }}</h1>
                    <p class="lead">{{ $course->intro }}</p>
                    @if($course->image && in_array($course->image_type,['image/jpg', 'image/jpeg', 'image/png', 'image/gif']))
                        <p>
                            <img src="{{$course->image}}" style="width:100%;max-width:600px;">
                        </p>
                    @endif
                    <h3>Modules</h3>
                    <table class="table table-striped table-hover">
                        @forelse ($subject->children as $module)
                            @include('admin.ndla-course-import.fragments.module-line')
                        @empty
                            <tr>
                                <td colspan="2"><h3>No content detected</h3></td>
                            </tr>
                        @endforelse
                    </table>
                    <a href="{{ route('admin.courseimport.export', $subjectId) }}"
                       class="btn btn-default">Export</a>
                </div>
            </div>
        </div>

        <!-- Import All Modal -->
        <div class="modal fade" id="importAllModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Really import all NDLA articles in this course?</h4>
                    </div>
                    <div class="modal-body">
                        <p>This may take a long time and will use a lot of resources in EdLib and NDLA.</p>
                        <p>Once started it cannot be stopped.</p>
                        <p>
                            Make sure the H5Ps are already imported.
                        </p>
                        <p>All articles will be owned by this user:
                        <p>
                            {{ $owner->displayName ?? 'NoName' }} (<a
                                    href="mailto:{{$owner->email?? 'NoEmail'}}">{{$owner->email??'NoEmail'}}</a>)
                        </p>
                        <p>
                            AuthId: {{ $owner->id }}
                        </p>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <form action="123{{ route('admin.courseimport.article.import', $subjectId) }}" method="get">
                            {{ csrf_field() }}
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                No, get me out of here!
                            </button>
                            <button type="submit" class="btn btn-lg btn-danger">Yes, I'm sure.</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1>This is not the environment i expected!</h1>
    @endif
@endsection
