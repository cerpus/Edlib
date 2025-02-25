<x-layout>
    <x-slot:title>LTI tools</x-slot:title>

    <p><a href="{{ route('admin.lti-tools.add') }}" class="btn btn-outline-primary">Add LTI tool</a></p>

    <div class="d-flex flex-column gap-3">
        @foreach ($tools as $tool)
            <article class="card lti-tool-card">
                <div class="card-header">
                    <strong class="card-title">{{ $tool->name }}</strong>
                </div>

                <div class="card-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th scope="row">ID</th>
                                <td><kbd>{{ $tool->id }}</kbd>
                            </tr>
                            <tr>
                                <th scope="row">Launch URL</th>
                                <td>{{ $tool->creator_launch_url }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Consumer key</th>
                                <td>
                                    @if ($tool->consumer_key)
                                        <kbd>{{ $tool->consumer_key }}</kbd>
                                    @else
                                        (none)
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">{{ trans('messages.url-slug') }}</th>
                                <td>{{ $tool->slug }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Associated resources</th>
                                <td>{{ $tool->content_versions_count }}</td>
                            </tr>
                            <tr>
                                <th scope="row">{{ trans('messages.send-full-name-to-lti-tool', ['site' => config('app.name')]) }}</th>
                                <td class="lti-tool-card-send-name">{{ $tool->send_name ? trans('messages.yes') : trans('messages.no') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">{{ trans('messages.send-email-to-lti-tool', ['site' => config('app.name')]) }}</th>
                                <td class="lti-tool-card-send-email">{{ $tool->send_email ? trans('messages.yes') : trans('messages.no') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h2 class="fs-4 mt-5">{{ trans('messages.extra-endpoints') }}</h2>

                    <table class="table table-striped">
                        <thead>
                            <th>{{ trans('messages.name') }}</th>
                            <th>{{ trans('messages.url-slug') }}</th>
                            <th>{{ trans('messages.lti-launch-url') }}</th>
                            <th>{{ trans('messages.admin-tool') }}
                            <th></th>
                        </thead>

                        <tbody>
                            @foreach ($tool->extras as $extra)
                                <tr>
                                    <td>{{ $extra->name }}</td>
                                    <td>{{ $extra->slug }}</td>
                                    <td>{{ $extra->lti_launch_url }}</td>
                                    <td>{{ $extra->admin ? trans('messages.yes') : trans('messages.no') }}</td>
                                    <td>
                                        @can('remove-extra', [$tool, $extra])
                                            <x-form
                                                action="{{ route('admin.lti-tools.remove-extra', [$tool, $extra]) }}"
                                                method="DELETE"
                                                class="d-inline"
                                                hx-delete="{{ route('admin.lti-tools.remove-extra', [$tool, $extra]) }}"
                                                hx-confirm="{{ trans('messages.confirm-lti-tool-extra-removal') }}"
                                            >
                                                <button class="btn btn-outline-danger btn-sm">&minus; {{ trans('messages.remove') }}</button>
                                            </x-form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        @can('add-extra', [$tool])
                            <caption>
                                <a href="{{ route('admin.lti-tools.add-extra', [$tool]) }}" class="btn btn-outline-success btn-sm">+ {{ trans('messages.add') }}</a>
                            </caption>
                        @endcan
                    </table>
                </div>

                @canany(['edit', 'remove'], $tool)
                    <div class="card-footer d-flex gap-2 justify-content-end">
                        @can('edit', $tool)
                            <a
                                href="{{ route('admin.lti-tools.edit', [$tool]) }}"
                                class="btn btn-sm btn-outline-success d-inline-flex gap-2"
                            >
                                <x-icon name="pencil" />
                                <span>{{ trans('messages.edit') }}</span>
                            </a>
                        @endcan

                        @can('remove', $tool)
                            <x-form :action="route('admin.lti-tools.remove', [$tool])" method="DELETE">
                                <x-form.button class="btn-sm btn-danger">
                                    {{ trans('messages.remove') }}
                                </x-form.button>
                            </x-form>
                        @endcan
                    </div>
                @endcanany
            </article>
        @endforeach
    </div>

    <div class="mb-3">
        {{ $tools->links() }}
    </div>
</x-layout>
