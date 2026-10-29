<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A simple and efficient way to manage your products and orders with OrderFlow.">
    <title>OrderFlow — Order Management</title>
    <style>[x-cloak] { display: none !important; }</style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-white font-sans text-slate-800 antialiased">
    <div
        x-data="{
            modal: @js(in_array(old('auth_modal'), ['login', 'register']) ? old('auth_modal') : null),
            lastTrigger: null,
            openModal(name) {
                this.lastTrigger = document.activeElement;
                this.modal = name;
                this.$nextTick(() => this.focusModal());
            },
            switchModal(name) {
                this.modal = name;
                this.$nextTick(() => this.focusModal());
            },
            closeModal() {
                this.modal = null;
                this.$nextTick(() => this.lastTrigger?.focus());
            },
            focusModal() {
                const fieldId = this.modal === 'login' ? 'login-email' : 'register-name';
                document.getElementById(fieldId)?.focus();
            },
            trapTab(event) {
                const dialog = this.$refs.dialog;
                const focusable = [...dialog.querySelectorAll('a[href], button:not([disabled]), input:not([disabled])')]
                    .filter((element) => element.offsetParent !== null);
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (!first || !last) return;
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }"
        x-init="$nextTick(() => { if (modal) focusModal() })"
        x-effect="document.body.classList.toggle('overflow-hidden', Boolean(modal))"
        x-on:keydown.escape.window="if (modal) closeModal()"
    >
        <header class="border-b border-slate-200 bg-white">
            <nav aria-label="Main navigation" class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-5 py-4 sm:px-8">
                <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                    Order<span class="text-blue-700">Flow</span>
                </a>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium">
                    <a href="{{ url('/') }}" class="text-slate-600 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Home</a>
                    @auth
                        @if (Route::has('dashboard'))
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-blue-700 px-3.5 py-2 text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Dashboard</a>
                        @endif
                    @else
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" x-on:click.prevent="openModal('login')" class="text-slate-600 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Login</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" x-on:click.prevent="openModal('register')" class="rounded-md bg-blue-700 px-3.5 py-2 text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Register</a>
                        @endif
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            <section class="border-b border-slate-200 bg-slate-50">
                <div class="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-20 lg:py-24">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold text-blue-700">Order management made simple</p>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Welcome to OrderFlow</h1>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-slate-600">A simple and efficient way to manage your products and orders.</p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                @if (Route::has('dashboard'))
                                    <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Get Started</a>
                                @endif
                            @else
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" x-on:click.prevent="openModal('register')" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Get Started</a>
                                @elseif (Route::has('login'))
                                    <a href="{{ route('login') }}" x-on:click.prevent="openModal('login')" class="inline-flex min-h-11 items-center justify-center rounded-md bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Get Started</a>
                                @endif
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" x-on:click.prevent="openModal('login')" class="inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Login</a>
                                @endif
                            @endauth
                        </div>
                    </div>
                </div>
            </section>


        </main>

        <footer class="border-t border-slate-200 bg-slate-50">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-5 py-6 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-8">
                <p class="font-semibold text-slate-800">OrderFlow</p>
                <p class="text-slate-500">© {{ date('Y') }} OrderFlow. All rights reserved.</p>
            </div>
        </footer>

        @guest
            <div
                x-cloak
                x-show="modal"
                x-on:click.self="closeModal()"
                x-on:keydown.tab="trapTab($event)"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4"
            >
                <section
                    x-ref="dialog"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="modal + '-title'"
                    tabindex="-1"
                    class="relative my-auto w-full max-w-md rounded-xl bg-white p-6 shadow-xl sm:p-8"
                >
                    <button type="button" x-on:click="closeModal()" aria-label="Close dialog" class="absolute right-4 top-4 rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>

                    @if (Route::has('login'))
                    <form x-show="modal === 'login'" x-ref="loginForm" method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="auth_modal" value="login">
                        <h2 id="login-title" class="pr-8 text-2xl font-semibold tracking-tight text-slate-900">Log in to OrderFlow</h2>
                        <p class="mt-2 text-sm text-slate-600">Enter your account details to continue.</p>

                        <div class="mt-6">
                            <label for="login-email" class="block text-sm font-medium text-slate-700">Email</label>
                            <input id="login-email" name="email" type="email" value="{{ old('auth_modal') === 'login' ? old('email') : '' }}" autocomplete="username" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'login' && $errors->has('email')) aria-invalid="true" aria-describedby="login-email-error" @endif>
                            @if (old('auth_modal') === 'login' && $errors->has('email'))
                                <p id="login-email-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between gap-3">
                                <label for="login-password" class="block text-sm font-medium text-slate-700">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Forgot password?</a>
                                @endif
                            </div>
                            <input id="login-password" name="password" type="password" autocomplete="current-password" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'login' && $errors->has('password')) aria-invalid="true" aria-describedby="login-password-error" @endif>
                            @if (old('auth_modal') === 'login' && $errors->has('password'))
                                <p id="login-password-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('password') }}</p>
                            @endif
                        </div>

                        <label for="login-remember" class="mt-4 inline-flex items-center gap-2 text-sm text-slate-600">
                            <input id="login-remember" name="remember" type="checkbox" value="1" @checked(old('remember')) class="rounded border-slate-300 text-blue-700 shadow-sm focus:ring-blue-700">
                            Remember me
                        </label>

                        <button type="submit" class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Login</button>
                        @if (Route::has('register'))
                        <p class="mt-5 text-center text-sm text-slate-600">Don't have an account?
                            <button type="button" x-on:click="switchModal('register')" class="font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Register</button>
                        </p>
                        @endif
                        <p class="mt-2 text-center text-xs text-slate-500"><a href="{{ route('login') }}" class="underline hover:text-slate-700">Open the full login page</a></p>
                    </form>
                    @endif

                    @if (Route::has('register'))
                        <form x-show="modal === 'register'" x-ref="registerForm" method="POST" action="{{ route('register') }}">
                            @csrf
                            <input type="hidden" name="auth_modal" value="register">
                            <h2 id="register-title" class="pr-8 text-2xl font-semibold tracking-tight text-slate-900">Create your account</h2>
                            <p class="mt-2 text-sm text-slate-600">Register to get started with OrderFlow.</p>

                            <div class="mt-6">
                                <label for="register-name" class="block text-sm font-medium text-slate-700">Name</label>
                                <input id="register-name" name="name" type="text" value="{{ old('auth_modal') === 'register' ? old('name') : '' }}" autocomplete="name" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'register' && $errors->has('name')) aria-invalid="true" aria-describedby="register-name-error" @endif>
                                @if (old('auth_modal') === 'register' && $errors->has('name'))
                                    <p id="register-name-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('name') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <label for="register-email" class="block text-sm font-medium text-slate-700">Email</label>
                                <input id="register-email" name="email" type="email" value="{{ old('auth_modal') === 'register' ? old('email') : '' }}" autocomplete="username" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'register' && $errors->has('email')) aria-invalid="true" aria-describedby="register-email-error" @endif>
                                @if (old('auth_modal') === 'register' && $errors->has('email'))
                                    <p id="register-email-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('email') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <label for="register-password" class="block text-sm font-medium text-slate-700">Password</label>
                                <input id="register-password" name="password" type="password" autocomplete="new-password" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'register' && $errors->has('password')) aria-invalid="true" aria-describedby="register-password-error" @endif>
                                @if (old('auth_modal') === 'register' && $errors->has('password'))
                                    <p id="register-password-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('password') }}</p>
                                @endif
                            </div>

                            <div class="mt-4">
                                <label for="register-password-confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                                <input id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="mt-1.5 block w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-700 focus:ring-blue-700" @if (old('auth_modal') === 'register' && $errors->has('password_confirmation')) aria-invalid="true" aria-describedby="register-password-confirmation-error" @endif>
                                @if (old('auth_modal') === 'register' && $errors->has('password_confirmation'))
                                    <p id="register-password-confirmation-error" class="mt-1.5 text-sm text-red-700">{{ $errors->first('password_confirmation') }}</p>
                                @endif
                            </div>

                            <button type="submit" class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">Register</button>
                            @if (Route::has('login'))
                                <p class="mt-5 text-center text-sm text-slate-600">Already have an account? <button type="button" x-on:click="switchModal('login')" class="font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Login</button></p>
                            @endif
                            <p class="mt-2 text-center text-xs text-slate-500"><a href="{{ route('register') }}" class="underline hover:text-slate-700">Open the full registration page</a></p>
                        </form>
                    @endif
                </section>
            </div>
        @endguest
    </div>
</body>
</html>
