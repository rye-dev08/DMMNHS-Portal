<x-layouts.guest :title="'Login'">
    <div class="flex min-h-screen w-full flex-col bg-[#f6f8fc] lg:flex-row">

        {{-- Left panel: branding / welcome (compact header on mobile) --}}
        <aside class="relative flex w-full flex-col overflow-hidden bg-gradient-to-br from-[#0a1633] via-[#0d2450] to-[#164aa8] p-8 lg:w-[42%] lg:min-h-screen lg:p-12 xl:p-16">
            <x-decorative-background />

            @if (file_exists(public_path('images/campus.jpg')))
                <img src="{{ asset('images/campus.jpg') }}" alt=""
                     class="absolute inset-x-0 bottom-0 hidden h-3/5 w-full object-cover object-bottom opacity-40 lg:block">
            @endif

            <div class="relative z-10 flex flex-1 flex-col lg:min-h-full">
                <x-brand />

                <div class="mt-8 lg:hidden">
                    <h1 class="text-2xl font-bold text-white">
                        Welcome to
                        <span class="bg-gradient-to-r from-[#38bdf8] to-[#2563eb] bg-clip-text text-transparent">DMMNHS Student Portal</span>
                    </h1>
                </div>

                <div class="hidden flex-1 flex-col justify-center py-20 lg:flex">
                    <p class="text-[15px] font-medium tracking-wide text-white/70">Welcome to</p>
                    <h1 class="mt-2 text-[44px] font-bold leading-[1.05]">
                        <span class="bg-gradient-to-r from-[#38bdf8] to-[#2563eb] bg-clip-text text-transparent">DMMNHS</span>
                        <span class="block text-white">Student Portal</span>
                    </h1>
                    <p class="mt-6 max-w-[34ch] text-[16px] leading-relaxed text-white/75">
                        Your gateway to academic excellence and endless possibilities.
                    </p>
                    <div class="mt-8 h-[3px] w-12 rounded-full bg-[#2f7df6]"></div>
                </div>

                <p class="mt-8 text-[12px] text-white/45 lg:mt-0">© 2026 DMMNHS Student Portal. All rights reserved.</p>
            </div>
        </aside>

        {{-- Right panel (58%) --}}
        <main class="relative flex w-full flex-1 items-center justify-center px-5 py-12 lg:w-[58%] lg:flex-none lg:px-10">
            <x-login-card title="Login to Your Account" subtitle="Enter your credentials to access your portal">
                <x-notice />
                <form method="POST" action="{{ route('login.attempt') }}" class="grid gap-5" data-validate>
                    @csrf

                    <x-form-input id="username" name="username" label="Email or Student ID"
                                  placeholder="Enter your email or student ID" :value="old('username')"
                                  autocomplete="username" required>
                        <x-slot name="trailing">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6"
                                 stroke="currentColor" class="h-5 w-5 text-slate-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </x-slot>
                    </x-form-input>

                    <div class="grid gap-1.5">
                        <x-password-input id="password" name="password" label="Password"
                                          placeholder="Enter your password" />
                        <div class="flex justify-end">
                            <a href="{{ route('contact') }}"
                               class="text-[13px] font-medium text-[#2563eb] transition hover:text-[#1d4ed8] hover:underline">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <x-primary-button>
                        Sign In <span aria-hidden="true">→</span>
                    </x-primary-button>

                    <div class="my-1 flex items-center gap-4">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="text-[12px] text-slate-400">or continue with</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <x-google-button
                        onclick="showNotice('Google sign-in is not configured yet. Please use your portal credentials.', 'info')">
                        Sign in with Google
                    </x-google-button>
                </form>
            </x-login-card>

            <p class="absolute bottom-6 left-0 right-0 px-5 text-center text-[13px] text-slate-500">
                Don't have an account?
                <a href="{{ route('contact') }}" class="font-medium text-[#2563eb] transition hover:text-[#1d4ed8] hover:underline">
                    Contact your administrator.
                </a>
            </p>
        </main>
    </div>
</x-layouts.guest>