<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — JCI Djougou Action</title>
    {{-- Styles compilés par Vite (npm run build) — plus aucun script tiers (faille M6). --}}
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-100 min-h-screen grid place-items-center px-4">
    <div class="text-center max-w-md">
        <div class="flex justify-center mb-6"><x-logo taille="w-16 h-16" fond="fonce" /></div>
        <div class="text-6xl font-black text-jci-900">@yield('code')</div>
        <h1 class="text-xl font-bold text-slate-800 mt-2">@yield('titre')</h1>
        <p class="text-slate-500 mt-2">@yield('message')</p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="bg-jci-900 text-white font-semibold px-5 py-2.5 rounded-lg hover:bg-jci-700">Accueil</a>
            <a href="{{ route('dashboard') }}" class="border px-5 py-2.5 rounded-lg hover:bg-white">Mon espace</a>
        </div>
    </div>
</body>
</html>
