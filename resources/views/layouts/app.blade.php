<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — HelpDesk Pro</title>

    <!-- Tailwind CSS CDN (w produkcji zamień na Vite build) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#3b82f6', dark: '#2563eb' }
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="h-full bg-gray-950 text-gray-100 font-sans antialiased">

<div class="flex h-full">
    {{-- ── Sidebar ─────────────────────────────────────────── --}}
    <aside class="w-60 bg-gray-900 border-r border-gray-800 flex flex-col flex-shrink-0">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-800">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-lg">🎫</div>
            <div>
                <div class="font-bold text-white text-sm">HelpDesk Pro</div>
                <div class="text-xs text-gray-500 font-mono">
                    {{ app()->environment('production') ? '● PROD' : '○ TEST' }}
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @php
                $navItems = [
                    ['route' => 'dashboard',    'icon' => '📊', 'label' => 'Dashboard'],
                    ['route' => 'tickets.index','icon' => '🎫', 'label' => 'Zgłoszenia'],
                ];
                if(auth()->user()->isAdmin()) {
                    $navItems[] = ['route' => 'companies.index', 'icon' => '🏢', 'label' => 'Firmy'];
                    $navItems[] = ['route' => 'reports.index',   'icon' => '📈', 'label' => 'Raporty'];
                    $navItems[] = ['route' => 'reports.billing', 'icon' => '💰', 'label' => 'Rozliczenia'];
                }
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors
                          {{ request()->routeIs(rtrim($item['route'], '.index') . '*')
                             ? 'bg-blue-600 text-white font-medium'
                             : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <span>{{ $item['icon'] }}</span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        {{-- User info --}}
        <div class="px-4 py-4 border-t border-gray-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-sm text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-xs text-gray-500 hover:text-red-400 transition-colors text-left px-1">
                    → Wyloguj
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main ──────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-h-0 overflow-auto">
        {{-- Top bar --}}
        <header class="bg-gray-900 border-b border-gray-800 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <h1 class="font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                @yield('header-actions')
                <a href="{{ route('tickets.create') }}"
                   class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    + Nowe zgłoszenie
                </a>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mx-6 mt-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm px-4 py-3 rounded-lg">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
