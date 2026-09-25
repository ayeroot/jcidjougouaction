@extends('layouts.auth')
@section('title', $reussi ? 'Adresse confirmée' : 'Lien invalide')
@section('content')
<h1 class="text-xl font-bold text-jci-900">{{ $reussi ? 'Adresse email confirmée' : 'Lien invalide ou expiré' }}</h1>
<p class="text-sm text-slate-500 mt-2">{{ $message }}</p>
<a href="{{ route('login') }}" class="inline-block mt-6 text-jci-600 hover:underline text-sm">← Aller à la connexion</a>
@endsection
