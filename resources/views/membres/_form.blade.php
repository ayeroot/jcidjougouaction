@php
    $statutsLabels = ['actif'=>'Actif','honoraire'=>'Honoraire','past_president'=>'Past-Président','membre_honneur'=>"Membre d'honneur"];
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium mb-1">Nom *</label>
        <input name="nom" value="{{ old('nom', $membre->nom) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Prénom *</label>
        <input name="prenom" value="{{ old('prenom', $membre->prenom) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date de naissance</label>
        <input type="date" name="date_naissance" value="{{ old('date_naissance', $membre->date_naissance?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Sexe</label>
        <select name="sexe" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">—</option>
            <option value="M" @selected(old('sexe',$membre->sexe)==='M')>Masculin</option>
            <option value="F" @selected(old('sexe',$membre->sexe)==='F')>Féminin</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nom de la promotion</label>
        <input name="promotion" value="{{ old('promotion', $membre->promotion) }}" placeholder="Ex : Promotion Excellence 2026" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Photo @unless($membre->exists)<span class="text-red-500">*</span>@endunless</label>
        @if ($membre->photo)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($membre->photo) }}" alt="" class="w-20 h-20 rounded-full object-cover mb-2 border">
        @endif
        <input type="file" name="photo" accept="image/*" @unless($membre->exists)required @endunless class="w-full text-sm border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <p class="text-xs text-slate-400 mt-1">JPG/PNG, 2 Mo max. @unless($membre->exists)Obligatoire à la création.@endunless</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Téléphone</label>
        <input name="telephone" value="{{ old('telephone', $membre->telephone) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $membre->email) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Fonction (CDL)</label>
        <select name="fonction" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">— Choisir une fonction —</option>
            @foreach (\App\Models\Membre::FONCTIONS as $f)
                <option value="{{ $f }}" @selected(old('fonction', $membre->fonction) === $f)>{{ $f }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Ville</label>
        <input name="ville" value="{{ old('ville', $membre->ville) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Adresse</label>
        <input name="adresse" value="{{ old('adresse', $membre->adresse) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Statut *</label>
        <select name="statut" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            @foreach ($statutsLabels as $val => $lbl)
                <option value="{{ $val }}" @selected(old('statut',$membre->statut)===$val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date d'adhésion</label>
        <input type="date" name="date_adhesion" value="{{ old('date_adhesion', $membre->date_adhesion?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
</div>
