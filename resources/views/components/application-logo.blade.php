@php
    $candidates = [
        'images/company-logo.png',
        'images/company-logo.svg',
        'images/logo.png',
        'images/logo.svg',
        'images/steg-logo.png',
        'images/steg-logo.svg',
    ];

    $logoPath = collect($candidates)->first(fn (string $path) => file_exists(public_path($path)));
    $logoUrl = $logoPath ? asset($logoPath) : null;
@endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="STEG" {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-lg bg-brand-primary px-3 py-2 text-sm font-semibold text-white']) }}>
        STEG
    </div>
@endif
