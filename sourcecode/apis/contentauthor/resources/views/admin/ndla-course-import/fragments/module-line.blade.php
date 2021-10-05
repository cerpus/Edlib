<tr>
    <td></td>
    <td>
        <h2>{{ $module->title }}</h2>
        @if($module->image && in_array($module->image_type,['image/jpg', 'image/jpeg', 'image/png', 'image/gif']))
            <p>
                <img src="{{$module->image}}" style="width:100%;max-width:1000px;">
            </p>
        @endif
        <p class="lead">{{ $module->intro }}</p>
        <table class="table table-striped table-hover">
            <tr>
                <th>Activities</th>
            </tr>
            @forelse($module->resources as $resource)
                @include('admin.ndla-course-import.fragments.resource-line')
            @empty
                <tr>
                    <td colspan="2">No resources / activities. This is probably because the article import has not been done.</td>
                </tr>
            @endforelse
        </table>
    </td>
</tr>
