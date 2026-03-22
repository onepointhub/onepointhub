@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700']) }}>
        {{ $status }}
    </div>
@endif
