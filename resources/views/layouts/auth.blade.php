<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JCI Djougou Action')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { jci: {
            50:'#ecfeff', 100:'#cffafe', 600:'#0891b2', 700:'#0e7490', 900:'#155e75' } } } } }
    </script>
</head>
<body class="bg-slate-100 min-h-screen grid place-items-center px-4">
    <div class="w-full max-w-md">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 font-bold text-jci-900 text-xl mb-6">
            <span class="inline-grid place-items-center w-10 h-10 rounded bg-jci-900 text-white font-black">JCI</span>
            Djougou Action
        </a>
        <div class="bg-white border rounded-2xl shadow-sm p-8">
            @if (session('ok'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-2 text-sm">{{ session('ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-2 text-sm">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </div>
        <p class="text-center text-xs text-slate-400 mt-6">© {{ date('Y') }} JCI Djougou Action</p>
    </div>
</body>
</html>
