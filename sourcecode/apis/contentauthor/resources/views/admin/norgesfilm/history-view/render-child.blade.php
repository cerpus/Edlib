<ul>
    <li>
        <button class="btn btn-link" onClick="openArticle('{{ $child->url }}');">{{ $child->title }}</button> ( {{ $child->created_at->toIso8601String() }} )
        <button class="btn btn-primary" onClick="replaceArticle('{{ $child->id }}');">Replace</button>
        @forelse($child->children as $theChildren)
            @include('admin.norgesfilm.history-view.render-child', ['child' => $theChildren])
        @empty
        @endforelse
    </li>
</ul>
