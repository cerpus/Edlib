<x-layout>
    <x-slot:title>{{ trans('messages.admins') }}</x-slot:title>

    <table class="table table-borderless table-striped">
        <thead>
        </thead>

        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->email }}</td>
                    <td>
                        <x-form action="{{ route('admin.admins.remove', [$user]) }}" method="DELETE">
                            <x-form.button class="btn-danger btn-sm">
                                {{ trans('messages.remove') }}
                            </x-form.button>
                        </x-form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $users->links() }}

    <hr>

    <x-form action="{{ route('admin.admins.add') }}">
        <x-form.field name="email" :label="trans('messages.email-address')" />

        <x-form.button class="btn-primary">{{ trans('messages.add') }}</x-form.button>
    </x-form>
</x-layout>
