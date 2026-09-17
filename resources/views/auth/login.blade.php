<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — JCI Djougou Action</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { jci: {
            100:'#cffafe', 600:'#0891b2', 700:'#0e7490', 900:'#155e75' } } } } }
    </script>
</head>
<body class="bg-slate-100 min-h-screen grid place-items-center px-4">
    <div class="w-full max-w-md">
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 font-bold text-jci-900 text-xl mb-6">
            <span class="inline-grid place-items-center w-10 h-10 rounded bg-jci-900 text-white font-black">JCI</span>
            Djougou Action
        </a>
        <div class="bg-white border rounded-2xl shadow-sm p-8">
            <h1 class="text-xl font-bold text-jci-900">Espace membre</h1>
            <p class="text-sm text-slate-500 mt-1">Connectez-vous pour accéder au tableau de bord.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-2 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300"> Se souvenir de moi
                </label>
                <button class="w-full bg-jci-900 text-white font-semibold py-2.5 rounded-lg hover:bg-jci-700">
                    Se connecter
                </button>
            </form>
        </div>
        <p class="text-center text-xs text-slate-400 mt-6">© {{ date('Y') }} JCI Djougou Action</p>
    </div>
</body>
</html>
