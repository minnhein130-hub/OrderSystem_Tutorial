<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrderFlow') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>[x-cloak] { display: none !important; }</style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-slate-800 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            <button
                x-cloak
                x-show="sidebarOpen"
                x-on:click="sidebarOpen = false"
                aria-label="Close navigation menu"
                class="fixed inset-0 z-40 bg-slate-900/30 lg:hidden"
            ></button>

            <aside
                id="dashboard-sidebar"
                class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                aria-label="Sidebar navigation"
            >
                <div class="flex h-16 shrink-0 items-center border-b border-slate-200 px-6">
                    <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                        Order<span class="text-blue-700">Flow</span>
                    </a>
                </div>

                <nav aria-label="Main navigation" class="flex-1 space-y-1 px-3 py-5 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" @class([
                        'flex items-center rounded-md px-3 py-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700',
                        'bg-blue-50 text-blue-700' => request()->routeIs('dashboard'),
                        'text-slate-600 hover:bg-slate-50 hover:text-blue-700' => ! request()->routeIs('dashboard'),
                    ])>
                        Dashboard
                    </a>

                    @if (Route::has('items.index'))
                        <a href="{{ route('items.index') }}" @class([
                            'flex items-center rounded-md px-3 py-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700',
                            'bg-blue-50 text-blue-700' => request()->routeIs('items.*'),
                            'text-slate-600 hover:bg-slate-50 hover:text-blue-700' => ! request()->routeIs('items.*'),
                        ])>
                            Items
                        </a>
                    @else
                        <span aria-disabled="true" title="Coming soon" class="flex cursor-not-allowed items-center justify-between rounded-md px-3 py-2.5 text-slate-400">
                            <span>Items</span><span class="text-xs font-normal">Coming soon</span>
                        </span>
                    @endif
                    @if (Route::has('orders.index'))
                        <a href="{{ route('orders.index') }}" @class([
                            'flex items-center rounded-md px-3 py-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700',
                            'bg-blue-50 text-blue-700' => request()->routeIs('orders.*'),
                            'text-slate-600 hover:bg-slate-50 hover:text-blue-700' => ! request()->routeIs('orders.*'),
                        ])>
                            Orders
                        </a>
                    @endif

                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" @class([
                            'flex items-center rounded-md px-3 py-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700',
                            'bg-blue-50 text-blue-700' => request()->routeIs('profile.*'),
                            'text-slate-600 hover:bg-slate-50 hover:text-blue-700' => ! request()->routeIs('profile.*'),
                        ])>
                            Profile
                        </a>
                    @endif
                </nav>

                <div class="border-t border-slate-200 p-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center rounded-md px-3 py-2.5 text-left text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-h-screen lg:pl-64">
                <header class="flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-3 sm:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            x-on:click="sidebarOpen = ! sidebarOpen"
                            aria-controls="dashboard-sidebar"
                            aria-label="Toggle navigation menu"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-300 text-slate-600 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2 lg:hidden"
                        >
                            <svg x-show="! sidebarOpen" viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <svg x-cloak x-show="sidebarOpen" viewBox="0 0 24 24" fill="none" class="h-5 w-5" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                        </button>
                        <div class="min-w-0">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h1 class="truncate text-lg font-semibold tracking-tight text-slate-900">{{ request()->routeIs('profile.*') ? 'Profile' : 'Dashboard' }}</h1>
                            @endisset
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 text-sm">
                        <span class="hidden font-medium text-slate-700 sm:inline">{{ Auth::user()->name }}</span>
                        @if (Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="rounded-md px-2 py-1.5 font-medium text-blue-700 hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700">Profile</a>
                        @endif
                    </div>
                </header>

                <main>
                    @include('items.partials.status')
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
