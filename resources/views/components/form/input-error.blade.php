@props(['message'])

@if ($message)
    <p {{ $attributes->merge(['class' => 'text-xs text-red-600 space-y-1']) }}>
        {{ $message }}
    </p>
@endif
