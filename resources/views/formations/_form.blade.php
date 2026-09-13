@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="grid sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Titre *</label>
        <input name="titre" value="{{ old('titre', $formation->titre) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Thème</label>
        <input name="theme" value="{{ old('theme', $formation->theme) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Statut *</label>
        <select name="statut" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            @foreach (['planifiee'=>'Planifiée','realisee'=>'Réalisée','annulee'=>'Annulée'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('statut',$formation->statut ?? 'planifiee')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date &amp; heure</label>
        <input type="datetime-local" name="date_formation" value="{{ old('date_formation', $formation->date_formation?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Lieu</label>
        <input name="lieu" value="{{ old('lieu', $formation->lieu) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Objectifs</label>
        <textarea name="objectifs" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('objectifs', $formation->objectifs) }}</textarea>
    </div>

    <div class="sm:col-span-2 border-t pt-4">
        <label class="block text-sm font-medium mb-1">Formateur</label>
        <select name="formateur_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">— Choisir un formateur existant —</option>
            @foreach ($formateurs as $f)
                <option value="{{ $f->id }}" @selected(old('formateur_id', $formation->formateur_id)==$f->id)>{{ $f->nom }} ({{ $f->type }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">…ou nouveau formateur</label>
        <input name="nouveau_formateur" value="{{ old('nouveau_formateur') }}" placeholder="Nom du formateur" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Type (si nouveau)</label>
        <select name="nouveau_formateur_type" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="externe">Externe</option>
            <option value="interne">Interne</option>
        </select>
    </div>
</div>
