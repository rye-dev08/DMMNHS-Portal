@props([
    'id' => '',
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'autocomplete' => 'off',
    'required' => true,
])
<div class="grid gap-1.5">
    <label for="{{ $id }}" class="text-[13px] font-semibold text-slate-600">{{ $label }}</label>
    <div class="relative">
        <input {{ $attributes->merge([
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'placeholder' => $placeholder,
            'value' => $value,
            'autocomplete' => $autocomplete,
            'required' => $required,
            'class' => 'h-[52px] w-full rounded-[10px] border border-slate-200 bg-white px-4 text-[14px] text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2563eb]/60 focus:ring-4 focus:ring-[#2563eb]/10',
        ]) }}>
        @if (isset($trailing))
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5">
                {{ $trailing }}
            </div>
        @endif
    </div>
    @error($name)
        <span class="text-[12px] text-red-500">{{ $message }}</span>
    @enderror
    {{ $slot }}
</div>