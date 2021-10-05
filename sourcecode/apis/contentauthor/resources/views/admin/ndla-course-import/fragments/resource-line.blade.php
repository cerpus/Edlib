<tr>
    <td>
        @if($idMapper = \App\NdlaIdMapper::byLaunchUrl(($resource->url ?? "If you see this there is a bug in resource-line.blade.php")))
            <a href="{{ route('article.show', $idMapper->ca_id) }}" target="_blank"><span class="glyphicon glyphicon-ok"></span> {{ $resource->title ?? "If you see this there is a bug in resource-line.blade.php"}}</a>
        @endif

    </td>
</tr>
