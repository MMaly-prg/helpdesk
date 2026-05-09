<!DOCTYPE html>
<html lang="pl" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie — HelpDesk Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-950 flex items-center justify-center">

<div class="w-full max-w-md px-6">
    {{-- Logo --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-2xl shadow-lg">🎫</div>
        <div>
            <div class="text-xl font-bold text-white">HelpDesk Pro</div>
            <div class="text-xs text-gray-500 font-mono">System Ticketowy</div>
        </div>
    </div>

    {{-- Formularz --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 shadow-2xl">
        <h2 class="text-lg font-semibold text-white mb-6">Zaloguj się</h2>

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg mb-5">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs text-gray-500 font-mono uppercase tracking-widest mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 placeholder-gray-600"
                    placeholder="serwisant@firma.pl">
            </div>

            <div>
                <label class="block text-xs text-gray-500 font-mono uppercase tracking-widest mb-2">Hasło</label>
                <input type="password" name="password" required
                    class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded border-gray-700 bg-gray-800">
                <label for="remember" class="text-sm text-gray-400">Zapamiętaj mnie</label>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition-colors text-sm">
                Zaloguj się →
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-700 mt-6">
        HelpDesk Pro · Środowisko: <span class="font-mono text-gray-600">{{ app()->environment() }}</span>
    </p>
</div>

</body>
</html>
