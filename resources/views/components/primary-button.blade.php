<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-[0.12em] text-white shadow-sm transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50', 'style' => 'background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);']) }}>
    {{ $slot }}
</button>
