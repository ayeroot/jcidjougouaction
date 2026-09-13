@extends('layouts.app')
@section('title', 'Planifier une formation')
@section('content')
<a href="{{ route('formations.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux formations</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Planifier une formation</h1>
<form method="POST" action="{{ route('formations.store') }}" class="bg-white border rounded-xl p-6">
    @csrf
    @include('formations._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer</button>
</form>
@endsection
