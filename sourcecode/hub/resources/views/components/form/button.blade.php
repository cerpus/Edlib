<button {{ $attributes->merge(['class' => 'btn', 'type' => 'submit']) }}>
    {{ $slot }}
</button>
