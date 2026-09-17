@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@php $current = isset($user) ? $user->getRoleNames()->first() : old('role'); @endphp

<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium mb-1">Nom complet *</label>
        <input name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email (identifiant) *</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Rôle *</label>
        <select name="role" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">— Choisir un rôle —</option>
            @foreach ($roles as $val => $lbl)
                <option value="{{ $val }}" @selected($current === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Fiche membre associée</label>
        <select name="membre_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">— Aucune —</option>
            @foreach ($membres as $m)
                <option value="{{ $m->id }}" @selected((string) old('membre_id', $user->membre_id ?? '') === (string) $m->id)>{{ $m->nom_complet ?: $m->fonction }}</option>
            @endforeach
        </select>
    </div>
</div>

@if (!isset($user))
    <p class="mt-4 text-sm text-slate-500 bg-slate-50 border rounded-lg px-4 py-3">
        Un <strong>mot de passe provisoire</strong> est généré automatiquement et envoyé par email
        à l'utilisateur avec son identifiant. Il pourra le modifier après sa première connexion.
    </p>
@endif
