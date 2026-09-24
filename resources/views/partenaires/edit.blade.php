@extends('layouts.app')
@section('title', 'Modifier le partenaire')
@section('content')
<a href="{{ route('partenaires.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Modifier {{ $partenaire->nom }}</h1>
<form method="POST" action="{{ route('partenaires.update', $partenaire) }}" enctype="multipart/form-data" class="bg-white border rounded-xl p-6 max-w-lg space-y-4">    @csrf @method('PUT')
    <div><label class="block text-sm font-medium mb-1">Nom *</label>
        <input name="nom" value="{{ old('nom',$partenaire->nom) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none"></div>
    <div><label class="block text-sm font-medium mb-1">Type</label>
        <input name="type" value="{{ old('type',$partenaire->type) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none"></div>
    <div><label class="block text-sm font-medium mb-1">Contact</label>
        <input name="contact" value="{{ old('contact',$partenaire->contact) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none"></div>
        <div><label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('description',$partenaire->description) }}</textarea></div>
<div><label class="block text-sm font-medium mb-1">Logo</label>
    @if($partenaire->logo)<img src="{{ asset('storage/'.$partenaire->logo) }}" class="w-16 h-16 rounded object-cover border mb-2">@endif
    <input type="file" name="logo" accept="image/*" class="w-full border rounded-lg px-3 py-2"></div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="public" value="1" @checked($partenaire->public) class="rounded border-slate-300"> Visible sur la vitrine</label>
    <button class="bg-jci-900 text-white px-6 py-2.5 rounded-lg hover:bg-jci-700">Mettre à jour</button>
</form>
@endsection
