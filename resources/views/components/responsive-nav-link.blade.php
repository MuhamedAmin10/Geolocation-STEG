@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-primary text-start text-base font-semibold text-brand-primary bg-brand-surface focus:outline-none focus:text-brand-primaryDark focus:bg-brand-surface focus:border-brand-primaryDark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:text-brand-primary hover:bg-slate-50 hover:border-brand-primary/35 focus:outline-none focus:text-brand-primary focus:bg-slate-50 focus:border-brand-primary/35 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
