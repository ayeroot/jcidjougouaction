@extends('layouts.auth')
@section('title', 'Mot de passe oublié')
@section('content')
<h1 class="text-xl font-bold text-jci-900">Mot de passe oublié</h1>
<p class="text-sm text-slate-500 mt-1">Saisissez votre email : un lien de réinitialisation vous sera envoyé.</p>
<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
    </div>
    <button class="w-full bg-jci-900 text-white font-semibold py-2.5 rounded-lg hover:bg-jci-700">Envoyer le lien</button>
</form>
<a href="{{ route('login') }}" class="inline-block mt-4 text-jci-600 hover:underline text-sm">← Retour à la connexion</a>
@endsection
