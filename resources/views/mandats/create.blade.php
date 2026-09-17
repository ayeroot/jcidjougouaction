@extends('layouts.app')
@section('title', 'Nouveau mandat')
@section('content')
<a href="{{ route('mandats.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Nouveau mandat</h1>
<form method="POST" action="{{ route('mandats.store') }}" enctype="multipart/form-data" class="bg-white border rounded-xl p-6">
    @csrf
    @include('mandats._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Créer</button>
</form>
@endsection
