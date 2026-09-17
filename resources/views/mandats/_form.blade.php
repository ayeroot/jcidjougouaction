@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium mb-1">Année *</label>
        <input name="annee" value="{{ old('annee', $mandat->annee) }}" required placeholder="2026" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Couleur du mandat</label>
        <input type="color" name="couleur" value="{{ old('couleur', $mandat->couleur ?: '#0891b2') }}" class="w-full h-11 border rounded-lg px-1 py-1">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Thème du mandat</label>
        <input name="theme" value="{{ old('theme', $mandat->theme) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date de début</label>
        <input type="date" name="date_debut" value="{{ old('date_debut', $mandat->date_debut?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date de fin</label>
        <input type="date" name="date_fin" value="{{ old('date_fin', $mandat->date_fin?->format('Y-m-d')) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Logo du mandat</label>
        @if ($mandat->logo)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mandat->logo) }}" class="h-16 mb-2 rounded border">@endif
        <input type="file" name="logo" accept="image/*" class="w-full text-sm border rounded-lg px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Photo de famille du CDL</label>
        @if ($mandat->photo_famille)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($mandat->photo_famille) }}" class="h-16 mb-2 rounded border">@endif
        <input type="file" name="photo_famille" accept="image/*" class="w-full text-sm border rounded-lg px-3 py-2">
    </div>
</div>
