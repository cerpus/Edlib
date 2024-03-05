<x-layout>
    <x-slot:title>LTI tools</x-slot:title>

    <p><a href="{{ route('admin.lti-tools.add') }}" class="btn btn-outline-primary">Add LTI tool</a></p>

    <div class="row row-cols-md-6 row-cols-lg-4 g-3">
        @foreach ($tools as $tool)
            <div class="col">
                <article class="card">
                    <div class="card-header">
                        <strong class="card-title">{{ $tool->name }}</strong>
                    </div>

                    <div class="card-body">
                        <dl>
                            <dt>Deep-Linking URL
                            <dd>{{ $tool->creator_launch_url }}
                            <dt>Consumer key
                            <dd>
                                @if ($tool->consumer_key)
                                    <kbd>{{ $tool->consumer_key }}</kbd>
                                @else
                                    (none)
                                @endif
                            <dt>Associated resources
                            <dd>{{ $tool->content_versions_count }}
                            <dt>{{ trans('messages.send-full-name-to-lti-tool', ['site' => config('app.name')]) }}</dt>
                            <dd>{{ $tool->send_name ? trans('messages.yes') : trans('messages.no') }}</dd>
                            <dt>{{ trans('messages.send-email-to-lti-tool', ['site' => config('app.name')]) }}</dt>
                            <dd>{{ $tool->send_email ? trans('messages.yes') : trans('messages.no') }}</dd>
                            <dt>{{ trans('messages.proxy-launch-to-lti-tool', ['site' => config('app.name')]) }}</dt>
                            <dd>{{ $tool->proxy_launch ? trans('messages.yes') : trans('messages.no') }}</dd>
                        </dl>
                    </div>

                    @can('remove', $tool)
                        <div class="card-footer">
                            <x-form :action="route('admin.lti-tools.remove', [$tool])" method="DELETE">
                                <x-form.button class="btn-sm btn-danger">
                                    {{ trans('messages.remove') }}
                                </x-form.button>
                            </x-form>
                        </div>
                    @endcan
                </article>
            </div>
        @endforeach
    </div>

    <div class="mb-3">
        {{ $tools->links() }}
    </div>
</x-layout>
