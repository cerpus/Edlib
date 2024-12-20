@if (config('h5p.isHubEnabled') === true)
    <form action="{{ route('admin.check-for-updates', isset($activeTab) ? ['activetab' => $activeTab] : []) }}" method="post">
        <div class="panel-body">
            @isset($contentTypeCacheUpdateAt)
                Last updated
                <strong>
                    {{ \Carbon\Carbon::createFromTimestamp($contentTypeCacheUpdateAt)->format('Y-m-d H:i:s e') }}
                </strong>
            @else
                Could not find last update time, click the 'Update' button to refresh the cache.
                This will also register the installation with the h5p.org hub if not registered.
            @endisset
        </div>
        <div class="panel-body">
            @csrf
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
@else
    Use of h5p.org hub is not enabled. To enable set <code>H5P_IS_HUB_ENABLED=true</code>
@endif
