@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
@php $labels = $roles; @endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">Gestion des utilisateurs</h1>
        <p class="text-slate-500 text-sm">{{ $users->total() }} compte(s). Attribuez rôles et privilèges depuis la fiche de chaque utilisateur.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">+ Nouveau compte</a>
</div>

{{-- Recherche + filtres --}}
<form method="GET" class="bg-white border rounded-xl p-4 mb-5 grid sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
    <input name="recherche" value="{{ request('recherche') }}" placeholder="Nom ou email…"
           class="border rounded-lg px-3 py-2 lg:col-span-2 focus:ring-2 focus:ring-jci-600 outline-none">
    <select name="role" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <option value="">Tous les rôles</option>
        @foreach ($labels as $val => $lbl)
            <option value="{{ $val }}" @selected(request('role')===$val)>{{ $lbl }}</option>
        @endforeach
    </select>
    <select name="etat" class="border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <option value="">Tous les états</option>
        <option value="actif" @selected(request('etat')==='actif')>Activés</option>
        <option value="attente" @selected(request('etat')==='attente')>En attente d'activation</option>
        <option value="suspendu" @selected(request('etat')==='suspendu')>Suspendus</option>
    </select>
    <div class="lg:col-span-4 flex gap-2">
        <button class="bg-jci-600 text-white px-4 py-2 rounded-lg hover:bg-jci-700">Filtrer</button>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border hover:bg-slate-50">Réinitialiser</a>
    </div>
</form>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3 font-semibold">Nom</th>
                <th class="px-4 py-3 font-semibold">Email</th>
                <th class="px-4 py-3 font-semibold">Rôle</th>
                <th class="px-4 py-3 font-semibold">État</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($users as $usr)
                <tr class="hover:bg-slate-50 align-top">
                    <td class="px-4 py-3 font-medium text-jci-900">{{ $usr->name }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $usr->email }}</td>
                    <td class="px-4 py-3">
                        @foreach ($usr->getRoleNames() as $r)
                            <span class="text-xs px-2 py-1 rounded bg-jci-100 text-jci-700">{{ $labels[$r] ?? $r }}</span>
                        @endforeach
                        @if ($usr->getDirectPermissions()->count())
                            <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-700" title="Permissions supplémentaires">+{{ $usr->getDirectPermissions()->count() }} perm.</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if (! $usr->actif)
                            <span class="text-xs px-2 py-1 rounded bg-red-100 text-red-700">Suspendu</span>
                        @elseif ($usr->activation_token)
                            <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700">En attente</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Activé</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $usr) }}" class="text-jci-600 hover:underline">Gérer</a>
                            @unless ($usr->id === auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle', $usr) }}"
                                      onsubmit="return confirm('{{ $usr->actif ? 'Suspendre' : 'Réactiver' }} le compte de {{ $usr->name }} ?')">
                                    @csrf
                                    <button class="{{ $usr->actif ? 'text-red-600' : 'text-green-600' }} hover:underline">{{ $usr->actif ? 'Suspendre' : 'Réactiver' }}</button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucun compte trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
