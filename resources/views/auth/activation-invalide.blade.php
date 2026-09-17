@extends('layouts.auth')
@section('title', 'Lien invalide')
@section('content')
<h1 class="text-xl font-bold text-jci-900">Lien invalide ou expiré</h1>
<p class="text-sm text-slate-500 mt-2">Ce lien d'activation n'est plus valable. Demandez à l'administrateur de vous renvoyer un lien d'activation.</p>
<a href="{{ route('login') }}" class="inline-block mt-6 text-jci-600 hover:underline text-sm">← Retour à la connexion</a>
@endsection
