@props(['type' => 'submit'])
<button {{ $attributes->merge([
    'type' => $type,
    'class' => 'inline-flex h-[52px] w-full items-center justify-center gap-2 rounded-[10px] bg-gradient-to-r from-[#0b3ef2] to-[#2f7df6] px-5 text-[15px] font-semibold text-white shadow-[0_8px_20px_rgba(37,99,235,0.28)] transition duration-200 hover:brightness-110 hover:shadow-[0_10px_26px_rgba(37,99,235,0.35)] focus:outline-none focus:ring-4 focus:ring-[#2563eb]/20 active:scale-[0.99]',
]) }}>
    {{ $slot }}
</button>