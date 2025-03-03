<x-layout>
    <x-slot:title>{{ trans('messages.contexts-for-lti-platform', ['platform' => $platform->name]) }}</x-slot:title>

    <p><a href="{{ route('admin.lti-platforms.index') }}">Back to LTI platforms</a></p>

    @if (count($platform->contexts) > 0)
        <ul>
            @foreach ($platform->contexts as $context)
                <li>{{ $context->name }} ({{ match ($context->pivot->role) {
                    \App\Enums\ContentRole::Reader => trans('messages.reader'),
                    \App\Enums\ContentRole::Editor => trans('messages.editor'),
                    \App\Enums\ContentRole::Owner => trans('messages.owner'),
                } }})</li>
            @endforeach
        </ul>
    @endif

    @if (count($available_contexts) > 0)
        <x-form action="{{ route('admin.lti-platforms.add-context', [$platform]) }}" method="PUT">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <x-form.field
                        name="context"
                        type="select"
                        emptyOption
                        required
                        :label="trans('messages.context')"
                        :options="$available_contexts"
                    />
                </div>

                <div class="col-12 col-lg-4">
                    <x-form.field
                        name="role"
                        type="select"
                        required
                        :label="trans('messages.role')"
                        :options="[
                                \App\Enums\ContentRole::Reader->value => trans('messages.reader'),
                                \App\Enums\ContentRole::Editor->value => trans('messages.editor'),
                                \App\Enums\ContentRole::Owner->value => trans('messages.owner'),
                            ]"
                    />
                </div>

                <div class="col-12">
                    <x-form.button class="btn-outline-primary">{{ trans('messages.add') }}</x-form.button>
                </div>
            </div>
        </x-form>
    @else
        <p>{{ trans('messages.no-available-contexts-to-add') }}</p>
    @endif
</x-layout>
