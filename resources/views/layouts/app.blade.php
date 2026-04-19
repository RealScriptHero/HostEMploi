<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFPPT - Emploi du Temps</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/ofppt_login.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false, sidebarTop: 0, sidebarHeight: '100vh' }"
    x-init="$nextTick(() => { if($refs.topBar){ const r = $refs.topBar.getBoundingClientRect(); sidebarTop = r.bottom; sidebarHeight = `calc(100vh - ${r.bottom}px)` } })">
    <div class="min-h-screen flex">
        @include('layouts.sidebar')

        <div class="flex-1 bg-gray-50 transition-all duration-300" id="main-content-area">
            {{-- Top Navigation Bar with Toggle --}}
            <div x-ref="topBar" class="bg-white border-b border-gray-200 px-6 py-2 flex items-center justify-between shadow-sm overflow-visible relative">
                <div class="flex items-center gap-2">
                    <button x-ref="toggleBtn" @click="sidebarOpen = !sidebarOpen; $nextTick(()=>{ if($refs.topBar){ const r = $refs.topBar.getBoundingClientRect(); sidebarTop = r.bottom; sidebarHeight = `calc(100vh - ${r.bottom}px)` } })"
                            class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
                            :title="sidebarOpen ? '{{ __('Hide sidebar') }}' : '{{ __('Show sidebar') }}'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    {{-- Brand Logo: OFPPT (moved from sidebar) --}}
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/OFPPTicon.png') }}" alt="OFPPT" style="height: 3rem; width: 12rem; object-fit: contain; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.05)); image-rendering: optimizeQuality;">
                    </div>
                </div>

              
                {{-- Right Side Icons --}}
                <div class="flex items-center gap-3">
                    {{-- Language Switcher --}}
                    <div class="relative" x-data="{ langMenuOpen: false }">
                        <button @click="langMenuOpen = !langMenuOpen" 
                                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors dark:text-gray-300 dark:hover:bg-gray-700" 
                                title="{{ __('Language') }}">
                            🌐
                        </button>
                        
                        {{-- Language Dropdown --}}
                        <div x-show="langMenuOpen"
                             x-cloak
                             @click.outside="langMenuOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50">
                            
                            <button onclick="switchLanguage('fr')" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors w-full text-left {{ app()->getLocale() === 'fr' ? 'bg-blue-50 text-blue-600' : '' }}">
                                🇫🇷 Français
                            </button>
                            
                            <button onclick="switchLanguage('en')" 
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors w-full text-left {{ app()->getLocale() === 'en' ? 'bg-blue-50 text-blue-600' : '' }}">
                                🇺🇸 English
                            </button>
                        </div>
                    </div>

                    {{-- User Dropdown --}}
                    <div class="flex items-center gap-2 pl-3 border-l border-gray-300 relative" x-data="{ userMenuOpen: false }">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-2 hover:opacity-80 transition-opacity focus:outline-none">
                            @php
                                $u = auth()->user();
                                $initials = '';
                                if ($u) {
                                    $first = $u->prenom ?? $u->nom ?? '';
                                    $last  = $u->nom ?? '';
                                    $initials = strtoupper(mb_substr($first, 0, 1) . ($last && $last !== $first ? mb_substr($last, 0, 1) : ''));
                                }
                            @endphp
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                {{ $initials ?: 'US' }}
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $u ? trim(($u->prenom ? $u->prenom.' ' : '').($u->nom ?? '')) : 'Utilisateur' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $u->email ?? '' }}
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 hidden md:block transition-transform" :class="{ 'rotate-180': userMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="userMenuOpen"
                             x-cloak
                             @click.outside="userMenuOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">

                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $u ? trim(($u->prenom ? $u->prenom.' ' : '').($u->nom ?? '')) : 'Utilisateur' }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">{{ $u->email ?? '' }}</p>
                            </div>

                            <a href="{{ route('parametres.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ __('Settings') }}
                            </a>

                            <div class="border-t border-gray-100 my-1"></div>

                            

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors w-full text-left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Site navigation under top menu (matches Formateurs layout) --}}
            @include('layouts.navigation')

            <main class="p-6" :class="{'bg-gray-50': !$el.querySelector('[style*=\"background-color: #f5f3f7\"]')}">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    // Language Switcher
    function switchLanguage(lang) {
        fetch('/parametres/language', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ language: lang })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error switching language:', error);
        });
    }

    // Theme Toggle - manage dark mode with localStorage
    function toggleTheme() {
        const isDark = localStorage.getItem('dark-mode') === 'true';
        const newValue = !isDark;
        localStorage.setItem('dark-mode', newValue ? 'true' : 'false');
        applyTheme(newValue);
        updateThemeIcon();
    }

    function applyTheme(isDark) {
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function updateThemeIcon() {
        const isDark = localStorage.getItem('dark-mode') === 'true';
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        if (isDark) {
            sunIcon?.classList.add('hidden');
            moonIcon?.classList.remove('hidden');
        } else {
            sunIcon?.classList.remove('hidden');
            moonIcon?.classList.add('hidden');
        }
    }

    // Initialize theme on page load
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = localStorage.getItem('dark-mode') === 'true';
        applyTheme(isDark);
        updateThemeIcon();
    });
    </script>
</body>
</html>
