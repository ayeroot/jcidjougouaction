@extends('layouts.app')
@section('title', 'Modifier la formation')
@section('content')
<a href="{{ route('formations.show', $formation) }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Modifier la formation</h1>
<form method="POST" action="{{ route('formations.update', $formation) }}" class="bg-white border rounded-xl p-6">
    @csrf @method('PUT')
    @include('formations._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Mettre à jour</button>
</form>
@endsection
