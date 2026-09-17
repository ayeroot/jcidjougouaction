@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
@php $labels = \App\Http\Controllers\Admin\UserController::ROLES; @endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-jci-900">Gestion des comptes</h1>
        <p class="text-slate-500 text-sm">{{ $users->total() }} compte(s). L'administrateur crée les comptes à partir des membres et attribue les rôles.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="bg-jci-900 text-white font-semibold px-4 py-2 rounded-lg hover:bg-jci-700 text-sm">+ Nouveau compte</a>
</div>

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
                    </td>
                    <td class="px-4 py-3">
                        @if (! $usr->actif)
                            <span class="text-xs px-2 py-1 rounded bg-slate-200 text-slate-600">Désactivé</span>
                        @elseif ($usr->activation_token)
                            <span class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700">En attente d'activation</span>
                        @else
                            <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-700">Activé</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $usr) }}" class="text-jci-600 hover:underline">Modifier</a>
                            @if ($usr->activation_token)
                                <form method="POST" action="{{ route('admin.users.activation', $usr) }}">@csrf
                                    <button class="text-amber-600 hover:underline">Renvoyer activation</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.reset', $usr) }}">@csrf
                                    <button class="text-amber-600 hover:underline">Réinit. mdp</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.users.toggle', $usr) }}">@csrf
                                <button class="text-slate-600 hover:underline">{{ $usr->actif ? 'Désactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $usr) }}" onsubmit="return confirm('Supprimer ce compte ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Suppr.</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Aucun compte.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
