<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'JCI Djougou Action')</title>
    {{-- Tailwind via CDN pour la phase de développement. En production, on passe au build Vite (voir README). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { jci: {
                50:'#f2f7fc', 100:'#EAF1F8', 600:'#1B6CA8', 700:'#12558f', 900:'#0B3D6B'
            } } } }
        }
    </script>
</head>
<body class="bg-white text-slate-800 antialiased">

    <header class="bg-jci-900 text-white sticky top-0 z-30 shadow">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg">
                <span class="inline-grid place-items-center w-9 h-9 rounded bg-white text-jci-900 font-black">JCI</span>
                <span>Djougou Action</span>
            </a>
            <nav class="flex items-center gap-6 text-sm">
                <a href="{{ route('home') }}#projets" class="hover:text-jci-100 hidden sm:inline">Projets</a>
                <a href="{{ route('home') }}#partenaires" class="hover:text-jci-100 hidden sm:inline">Partenaires</a>
                <a href="{{ route('inscription.create') }}" class="bg-white text-jci-900 font-semibold px-4 py-2 rounded-lg hover:bg-jci-100">Devenir membre</a>
                <a href="{{ route('login') }}" class="border border-white/40 px-4 py-2 rounded-lg hover:bg-white/10">Espace membre</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-300 mt-20">
        <div class="max-w-6xl mx-auto px-4 py-10 grid sm:grid-cols-3 gap-8 text-sm">
            <div>
                <div class="text-white font-bold text-lg mb-2">JCI Djougou Action</div>
                <p class="text-slate-400">Organisation Locale Membre de la Jeune Chambre Internationale, Djougou, Bénin.</p>
            </div>
            <div>
                <div class="text-white font-semibold mb-2">Navigation</div>
                <ul class="space-y-1 text-slate-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Accueil</a></li>
                    <li><a href="{{ route('inscription.create') }}" class="hover:text-white">Inscription</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">Espace membre</a></li>
                </ul>
            </div>
            <div>
                <div class="text-white font-semibold mb-2">Contact</div>
                <p class="text-slate-400">Djougou, Donga — Bénin</p>
            </div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs text-slate-500">
            © {{ date('Y') }} JCI Djougou Action — Tous droits réservés.
        </div>
    </footer>

</body>
</html>
