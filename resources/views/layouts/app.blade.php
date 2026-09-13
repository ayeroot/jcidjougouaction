<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace') — JCI Djougou Action</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { jci: {
                50:'#f2f7fc', 100:'#EAF1F8', 600:'#1B6CA8', 700:'#12558f', 900:'#0B3D6B'
            } } } }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@php $u = auth()->user(); @endphp

<div class="min-h-screen flex">

    {{-- Barre latérale --}}
    <aside class="w-64 bg-jci-900 text-white flex-col hidden md:flex">
        <div class="h-16 flex items-center gap-2 px-5 border-b border-white/10 font-bold">
            <span class="inline-grid place-items-center w-8 h-8 rounded bg-white text-jci-900 font-black text-sm">JCI</span>
            Djougou Action
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            @php
                function navlink($route, $label, $active) {
                    $base = 'flex items-center gap-3 px-3 py-2 rounded-lg ';
                    $cls = $active ? 'bg-white/15 font-semibold' : 'hover:bg-white/10 text-slate-200';
                    return '<a href="'.$route.'" class="'.$base.$cls.'">'.$label.'</a>';
                }
            @endphp

            {!! navlink(route('dashboard'), 'Tableau de bord', request()->routeIs('dashboard')) !!}
            {!! navlink(route('membres.index'), 'Membres', request()->routeIs('membres.*')) !!}

            @role('vpcd|vpf|president')
                {!! navlink(route('postulants.index'), 'Recrutement', request()->routeIs('postulants.*')) !!}
            @endrole

            @role('vpf|president')
                {!! navlink(route('formations.index'), 'Formations', request()->routeIs('formations.*')) !!}
            @endrole

            @role('tresorier|president')
                {!! navlink(route('finances.index'), 'Finances', request()->routeIs('finances.*')) !!}
            @endrole

            {{-- Modules à venir (lots suivants) --}}
            @php
                $bientot = ['Projets', 'Partenaires', 'Archives', 'Efficacité 100%'];
            @endphp
            <div class="pt-4 mt-4 border-t border-white/10 text-xs uppercase tracking-wide text-slate-400 px-3 mb-1">Prochains lots</div>
            @foreach ($bientot as $b)
                <span class="flex items-center justify-between px-3 py-2 rounded-lg text-slate-400 cursor-not-allowed">
                    {{ $b }}
                    <span class="text-[10px] bg-white/10 px-1.5 py-0.5 rounded">bientôt</span>
                </span>
            @endforeach
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        {{-- En-tête --}}
        <header class="h-16 bg-white border-b flex items-center justify-between px-4 md:px-6">
            <div class="font-semibold text-jci-900">@yield('title', 'Espace')</div>
            <div class="flex items-center gap-4">
                <div class="text-right leading-tight hidden sm:block">
                    <div class="text-sm font-medium">{{ $u->name }}</div>
                    <div class="text-xs text-slate-500">{{ $u->getRoleNames()->map(fn($r)=>ucfirst($r))->implode(', ') }}</div>
                </div>
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

</body>
</html>
