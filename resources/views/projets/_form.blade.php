@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
<div class="grid sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Titre *</label>
        <input name="titre" value="{{ old('titre', $projet->titre) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('description', $projet->description) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Statut *</label>
        <select name="statut" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            @foreach (['a_venir'=>'À venir','en_cours'=>'En cours','termine'=>'Terminé'] as $v=>$l)
                <option value="{{ $v }}" @selected(old('statut',$projet->statut ?? 'en_cours')===$v)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Avancement (%)</label>
        <input type="number" name="avancement" min="0" max="100" value="{{ old('avancement', $projet->avancement ?? 0) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Responsable</label>
        <select name="responsable_id" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            <option value="">— Aucun —</option>
            @foreach ($membres as $m)<option value="{{ $m->id }}" @selected(old('responsable_id',$projet->responsable_id)==$m->id)>{{ $m->nom_complet }}</option>@endforeach
        </select>
    </div>
    <div class="flex items-center gap-2 pt-6">
        <input type="checkbox" name="public" value="1" @checked(old('public', $projet->public ?? true)) class="rounded border-slate-300">
        <label class="text-sm">Visible sur la vitrine publique</label>
    </div>
</div>
