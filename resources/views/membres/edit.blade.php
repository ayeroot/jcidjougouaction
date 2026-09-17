@extends('layouts.app')
@section('title', 'Modifier le membre')
@section('content')
<a href="{{ route('membres.show', $membre) }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour à la fiche</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Modifier {{ $membre->nom_complet }}</h1>
<form method="POST" action="{{ route("membres.update", $membre) }}" enctype="multipart/form-data" class="bg-white border rounded-xl p-6">
    @csrf @method('PUT')
    @include('membres._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Mettre à jour</button>
</form>
@endsection
