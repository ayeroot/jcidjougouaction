@extends('layouts.app')
@section('title', 'Rôles & permissions')
@section('content')

<h1 class="text-2xl font-bold text-jci-900 mb-1">Rôles & permissions</h1>
<p class="text-slate-500 text-sm mb-6">Ajustez les permissions accordées par chaque rôle. Toute modification s'applique immédiatement à <strong>tous</strong> les utilisateurs de ce rôle — sans modification du code.</p>

<div class="space-y-4">
    @foreach ($roles as $role)
        @php $perms = $role->permissions->pluck('name')->all(); @endphp
        <details class="bg-white border rounded-xl" @if($loop->first) open @endif>
            <summary class="cursor-pointer px-6 py-4 font-semibold text-jci-900 flex items-center justify-between">
                <span>{{ $labels[$role->name] ?? $role->name }}
                    <span class="text-xs font-normal text-slate-400">({{ count($perms) }} permission(s))</span>
                </span>
                <span class="text-xs text-slate-400">{{ $role->name }}</span>
            </summary>
            <div class="px-6 pb-6">
                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf @method('PUT')
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($catalogue as $groupe => $liste)
                            <div class="border rounded-lg p-3">
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">{{ $groupe }}</div>
                                <div class="space-y-1.5">
                                    @foreach ($liste as $slug => $libelle)
                                        <label class="flex items-start gap-2 text-sm">
                                            <input type="checkbox" name="permissions[]" value="{{ $slug }}"
                                                   @checked(in_array($slug, $perms, true))
                                                   class="mt-0.5 rounded border-slate-300 text-jci-600 focus:ring-jci-600">
                                            <span>{{ $libelle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="mt-4 bg-jci-900 text-white font-semibold px-5 py-2 rounded-lg hover:bg-jci-700 text-sm">Enregistrer</button>
                </form>
                <form method="POST" action="{{ route('admin.roles.reset', $role) }}" class="mt-2"
                      onsubmit="return confirm('Réinitialiser ce rôle aux permissions par défaut définies dans le code ?')">
                    @csrf
                    <button class="border px-5 py-2 rounded-lg hover:bg-slate-50 text-sm text-slate-600">Réinitialiser (défauts)</button>
                </form>
            </div>
        </details>
    @endforeach
</div>
@endsection
