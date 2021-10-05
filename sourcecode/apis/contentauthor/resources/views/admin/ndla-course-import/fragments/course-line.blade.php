<tr>
    <td>
        <div style="margin-bottom: 20px !important;">
            <div class="h2">
                {{ $course->title }}
            </div>
            <span class="pull-right">
            <a href="{{ route('admin.courseimport.export', [$subjectId, $course->id]) }}"
               class="btn btn-danger btn-lg pull-right">Export</a>
            <a href="{{route('admin.courseimport.article.import',[ $subjectId, $course->id])}}" class="btn btn-danger">
                Import content</a>
            </span>
            @php
                $courseExport = \App\CourseExport::byNdlaId($course->id)
            @endphp
            @if($courseExport)
                @if($courseExport->edit_url ?? null)
                    <a href="{{ $courseExport->edit_url }}" target="_blank" class="btn btn-success">Edit in
                        EdStep</a>
                @endif
            @endif
            <div>{{ $courseExport->message ? $courseExport->updated_at->setTimeZone('Europe/Oslo')->toIso8601String() .': '. $courseExport->message  :'No log messages'  }}</div>
        </div>
    @if($course->image && in_array($course->image_type,['image/jpg', 'image/jpeg', 'image/png', 'image/gif']))
            <p>
                <img src="{{$course->image}}" style="width:100%;max-width:1000px;">
            </p>
        @endif
        <p class="lead">{{ $course->intro }}</p>
        <table class="table table-striped table-hover">
            <tr>
                <th style="width:15%;"></th>
                <th>Modules</th>
            </tr>
            @forelse($course->children as $module)
                @include('admin.ndla-course-import.fragments.module-line')
            @empty
                <tr>
                    <td colspan="2">No resources / activities. This is probably because the article import has not been
                        done.
                    </td>
                </tr>
            @endforelse
        </table>
    </td>
</tr>
