@extends('layouts.app')
@section('title', 'Nouveau compte')
@section('content')
<a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-jci-900">← Retour aux comptes</a>
<h1 class="text-2xl font-bold text-jci-900 mt-2 mb-6">Créer un compte</h1>
<form method="POST" action="{{ route('admin.users.store') }}" class="bg-white border rounded-xl p-6">
    @csrf
    @include('admin.users._form')
    <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Créer et envoyer l'email</button>
</form>
@endsection
