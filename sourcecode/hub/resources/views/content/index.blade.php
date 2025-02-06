<x-layout current="shared-content" :layout="$filter->getLayout()">
    <x-slot:title>{{ trans('messages.explore') }}</x-slot:title>

    <x-content.search :$contents :$filter />
</x-layout>
