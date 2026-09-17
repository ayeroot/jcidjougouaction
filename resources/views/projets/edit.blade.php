@extends('layouts.app')
@section('title', 'Modifier le projet')
@section('content')
<a href="{{ route('projets.show', $projet) }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Modifier le projet</h1>
<form method="POST" action="{{ route('projets.update', $projet) }}" class="bg-white border rounded-xl p-6">
    @csrf @method('PUT') @include('projets._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Mettre à jour</button>
</form>
@endsection
