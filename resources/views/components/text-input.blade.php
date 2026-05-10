@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-slate-800 shadow-sm transition focus:border-sky-600 focus:ring-sky-600 disabled:cursor-not-allowed disabled:bg-slate-100']) }}>
