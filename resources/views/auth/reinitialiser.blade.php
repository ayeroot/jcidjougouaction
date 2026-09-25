@extends('layouts.auth')
@section('title', 'Réinitialiser le mot de passe')
@section('content')
<h1 class="text-xl font-bold text-jci-900">Nouveau mot de passe</h1>
<form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
        <input type="password" name="password" required minlength="10" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <p class="text-xs text-slate-500 mt-1">10 caractères minimum, avec des lettres et des chiffres.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Confirmer</label>
        <input type="password" name="password_confirmation" required minlength="10" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <button class="w-full bg-jci-900 text-white font-semibold py-2.5 rounded-lg hover:bg-jci-700">Réinitialiser</button>
</form>
@endsection
