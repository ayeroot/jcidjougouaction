@extends('layouts.app')
@section('title', 'Modifier le compte')
@section('content')
<a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux comptes</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-1">Compte de {{ $user->name }}</h1>
<p class="text-slate-500 text-sm mb-6">L'administrateur peut modifier l'email et le rôle. Les informations personnelles se modifient sur la fiche membre.</p>

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="bg-white border rounded-xl p-6">
    @csrf @method('PUT')
    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium mb-1">Email (identifiant) *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Rôle *</label>
            <select name="role" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                @foreach ($roles as $val => $lbl)
                    <option value="{{ $val }}" @selected($user->getRoleNames()->first() === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 text-sm text-slate-500">
            Fiche membre : {{ $user->membre?->nom_complet ?: '—' }} ·
            État : {!! $user->estActive() ? '<span class="text-green-600">activé</span>' : '<span class="text-amber-600">non activé / désactivé</span>' !!}
        </div>
    </div>
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer</button>
</form>
@endsection
