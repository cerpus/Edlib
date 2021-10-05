<h2>Old Content</h2>

@forelse ($oldContent as $libraryName => $library)
	@if (isset($library->content) && $library->content != null)
	    <div><b>{{ $library->name }}</b> <a href="/admin/content/library/{{ $library->id }}/upgrade" ><i class="fa fa-rocket"></a></i></div>
	    <ul>
	        @forelse ( $library->content as $content)
	            <li>{{ $content->title }}</li>
	        @empty
	            <p>No old content</p>
	        @endforelse
	    </ul>
	@endif
@empty
    <p>No old content</p>
@endforelse
