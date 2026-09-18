@extends('layouts.app')
@section('title', 'Fiche utilisateur')
@section('content')

<a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux utilisateurs</a>
<div class="flex items-center justify-between mt-2 mb-1">
    <h1 class="text-2xl font-bold text-jci-900">{{ $user->name }}</h1>
    <span class="text-xs px-2 py-1 rounded {{ $user->actif ? ($user->activation_token ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') : 'bg-red-100 text-red-700' }}">
        {{ $user->actif ? ($user->activation_token ? "En attente d'activation" : 'Activé') : 'Suspendu' }}
    </span>
</div>
<p class="text-slate-500 text-sm mb-6">Fiche membre : {{ $user->membre?->nom_complet ?: '—' }}</p>

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@if ($estMoi)
    <div class="mb-5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
        🔒 Il s'agit de votre propre compte : pour des raisons de sécurité, vous ne pouvez pas modifier votre rôle ni vos permissions ici.
    </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf @method('PUT')

    <div class="bg-white border rounded-xl p-6 mb-5">
        <h2 class="font-semibold text-jci-900 mb-3">Identité & rôle</h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium mb-1">Email (identifiant) *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                <p class="text-xs text-slate-400 mt-1">Seul l'administrateur peut modifier l'email.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Rôle *</label>
                <select name="role" required @disabled($estMoi) class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none @if($estMoi) bg-slate-50 text-slate-500 @endif">
                    @foreach ($roles as $val => $lbl)
                        <option value="{{ $val }}" @selected($user->getRoleNames()->first() === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Le rôle définit le niveau de privilèges de base.</p>
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-xl p-6 mb-5">
        <h2 class="font-semibold text-jci-900 mb-1">Privilèges</h2>
        <p class="text-xs text-slate-400 mb-4">
            Les permissions <span class="text-jci-700 font-medium">accordées par le rôle</span> sont automatiques.
            Cochez des cases pour <span class="text-purple-700 font-medium">accorder des permissions supplémentaires</span> à cet utilisateur.
            Pour retirer une permission du rôle, changez son rôle.
        </p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($catalogue as $groupe => $perms)
                <div class="border rounded-lg p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">{{ $groupe }}</div>
                    <div class="space-y-2">
                        @foreach ($perms as $slug => $libelle)
                            @php
                                $viaRole   = in_array($slug, $permsDuRole, true);
                                $direct    = in_array($slug, $permsDirectes, true);
                            @endphp
                            <label class="flex items-start gap-2 text-sm {{ $estMoi ? 'opacity-70' : '' }}">
                                <input type="checkbox" name="permissions[]" value="{{ $slug }}"
                                       @checked($direct)
                                       @disabled($estMoi || $viaRole)
                                       class="mt-0.5 rounded border-slate-300 text-jci-600 focus:ring-jci-600">
                                <span>
                                    {{ $libelle }}
                                    @if ($viaRole)
                                        <span class="block text-[11px] text-jci-600">✓ via le rôle</span>
                                    @elseif ($direct)
                                        <span class="block text-[11px] text-purple-600">✓ supplémentaire</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @unless ($estMoi)
        <button class="bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer les privilèges</button>
    @else
        <button class="bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer l'email</button>
    @endunless
</form>

{{-- Actions sensibles --}}
<div class="bg-white border rounded-xl p-6 mt-5">
    <h2 class="font-semibold text-jci-900 mb-3">Compte</h2>
    <div class="flex flex-wrap gap-2">
        @if ($user->activation_token)
            <form method="POST" action="{{ route('admin.users.activation', $user) }}">@csrf
                <button class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Renvoyer l'activation</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.users.reset', $user) }}">@csrf
                <button class="text-sm border px-4 py-2 rounded-lg hover:bg-slate-50">Réinitialiser le mot de passe</button>
            </form>
        @endif
        @unless ($estMoi)
            <form method="POST" action="{{ route('admin.users.toggle', $user) }}"
                  onsubmit="return confirm('{{ $user->actif ? 'Suspendre' : 'Réactiver' }} ce compte ?')">
                @csrf
                <button class="text-sm border px-4 py-2 rounded-lg {{ $user->actif ? 'border-red-200 text-red-600 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                    {{ $user->actif ? 'Suspendre le compte' : 'Réactiver le compte' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer définitivement ce compte ?')">
                @csrf @method('DELETE')
                <button class="text-sm border border-red-200 text-red-600 px-4 py-2 rounded-lg hover:bg-red-50">Supprimer</button>
            </form>
        @endunless
    </div>
</div>
@endsection
