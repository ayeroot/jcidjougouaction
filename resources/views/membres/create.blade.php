@extends('layouts.app')
@section('title', 'Nouveau membre')
@section('content')
<a href="{{ route('membres.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Nouveau membre</h1>
<form method="POST" action="{{ route("membres.store") }}" enctype="multipart/form-data" class="bg-white border rounded-xl p-6">
    @csrf
    @include('membres._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer</button>
</form>
@endsection
