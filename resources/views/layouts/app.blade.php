<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace') — JCI Djougou Action</title>
    {{-- Styles compilés par Vite (npm run build) — plus aucun script tiers (faille M6). --}}
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@php $u = auth()->user(); @endphp

<div class="min-h-screen flex">

    {{-- Voile sombre (mobile uniquement, sous le tiroir) --}}
    <div id="sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 z-30 bg-black/50 hidden md:hidden"></div>

    {{-- Barre latérale : tiroir coulissant sur mobile, fixe sur ordinateur --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-jci-900 text-white flex flex-col transform -translate-x-full transition-transform duration-200 ease-in-out md:static md:translate-x-0">
        <div class="h-16 flex items-center justify-between gap-2 px-5 border-b border-white/10 font-bold">
            <div class="flex items-center gap-2">
                <x-logo taille="w-8 h-8" />
                Djougou Action
            </div>
            {{-- Fermer (mobile) --}}
            <button type="button" onclick="toggleSidebar(false)" class="md:hidden text-white/70 hover:text-white text-3xl leading-none" aria-label="Fermer le menu">&times;</button>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
            @php
                // Closure (et non « function navlink() ») : une fonction nommée déclarée dans
                // une vue plante si la vue est rendue deux fois dans le même processus.
                $navlink = function ($route, $label, $active) {
                    $base = 'flex items-center gap-3 px-3 py-2 rounded-lg ';
                    $cls = $active ? 'bg-white/15 font-semibold' : 'hover:bg-white/10 text-slate-200';
                    return '<a href="'.e($route).'" class="'.$base.$cls.'">'.e($label).'</a>';
                };
            @endphp

            {!! $navlink(route('dashboard'), 'Tableau de bord', request()->routeIs('dashboard')) !!}
            {!! $navlink(route('membres.index'), 'Membres', request()->routeIs('membres.*')) !!}
            {!! $navlink(route('anniversaires.index'), '🎂 Anniversaires', request()->routeIs('anniversaires.*')) !!}

            @can('postulants.voir')
                {!! $navlink(route('postulants.index'), 'Recrutement', request()->routeIs('postulants.*')) !!}
            @endcan

            @can('formations.voir')
                {!! $navlink(route('formations.index'), 'Formations', request()->routeIs('formations.*')) !!}
            @endcan

            @can('finances.voir')
                {!! $navlink(route('finances.index'), 'Finances', request()->routeIs('finances.*')) !!}
            @endcan

            @can('projets.gerer')
                {!! $navlink(route('projets.index'), 'Projets', request()->routeIs('projets.*')) !!}
            @endcan

            @can('partenaires.voir')
                {!! $navlink(route('partenaires.index'), 'Partenaires', request()->routeIs('partenaires.*')) !!}
            @endcan

            @can('archives.voir')
                {!! $navlink(route('archives.index'), 'Archives', request()->routeIs('archives.*')) !!}
            @endcan

            @can('efficacite.voir')
                {!! $navlink(route('efficacite.index'), '100% efficacité', request()->routeIs('efficacite.*')) !!}
            @endcan

            @can('historique.voir')
                {!! $navlink(route('historique.index'), 'Historique', request()->routeIs('historique.*')) !!}
            @endcan

            @can('mandats.voir')
                {!! $navlink(route('mandats.index'), 'Mandats', request()->routeIs('mandats.*')) !!}
            @endcan

            @canany(['utilisateurs.gerer', 'vitrine.gerer'])
                <div class="pt-4 mt-4 border-t border-white/10 text-xs uppercase tracking-wide text-slate-400 px-3 mb-1">Administration</div>
            @endcanany
            @can('utilisateurs.gerer')
                {!! $navlink(route('admin.users.index'), 'Utilisateurs', request()->routeIs('admin.users.*')) !!}
                {!! $navlink(route('admin.roles.index'), 'Rôles & permissions', request()->routeIs('admin.roles.*')) !!}
            @endcan
            @can('vitrine.gerer')
                {!! $navlink(route('admin.vitrine.edit'), 'Site vitrine', request()->routeIs('admin.vitrine.*')) !!}
            @endcan
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        {{-- En-tête --}}
        <header class="h-16 bg-white border-b flex items-center justify-between px-4 md:px-6">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Bouton menu (mobile) --}}
                <button type="button" onclick="toggleSidebar(true)" class="md:hidden text-jci-900 shrink-0" aria-label="Ouvrir le menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="font-semibold text-jci-900 truncate">@yield('title', 'Espace')</div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('profil.edit') }}" class="text-right leading-tight hidden sm:block hover:opacity-80">
                    <div class="text-sm font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-slate-500">{{ $u->getRoleNames()->map(fn($r)=>ucfirst($r))->implode(', ') }} · Mon profil</div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-slate-600 hover:text-jci-900 border rounded-lg px-3 py-1.5">Déconnexion</button>
                </form>
            </div>
        </header>

        <main class="p-4 md:p-6 max-w-6xl w-full mx-auto">
            @if (session('ok'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('ok') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar(open) {
        const sb = document.getElementById('sidebar');
        const ov = document.getElementById('sidebar-overlay');
        sb.classList.toggle('-translate-x-full', !open);
        ov.classList.toggle('hidden', !open);
    }
</script>

</body>
</html>
