@props(['title' => 'Login to Your Account', 'subtitle' => ''])
<div {{ $attributes->merge(['class' => 'w-full max-w-[440px] rounded-[16px] border border-slate-100 bg-white p-8 shadow-[0_18px_50px_rgba(15,23,42,0.08)] sm:p-10']) }}>
    <div class="mb-8 flex flex-col items-center text-center">
        <div class="mb-5 flex h-[70px] w-[70px] items-center justify-center rounded-[14px] bg-[#dbeafe]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"
                 class="h-8 w-8 text-[#2563eb]">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-[#0f1b3d]">{{ $title }}</h1>
        @if ($subtitle !== '')
            <p class="mt-2 text-[14px] text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</div>