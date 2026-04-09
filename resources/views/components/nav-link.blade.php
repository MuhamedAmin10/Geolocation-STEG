@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-2 text-sm font-semibold leading-5 text-brand-primary border-b-2 border-brand-primary focus:outline-none transition duration-150 ease-in-out'
            : 'inline-flex items-center px-3 py-2 text-sm font-medium leading-5 text-slate-600 border-b-2 border-transparent hover:text-brand-primary hover:border-brand-primary focus:outline-none transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
