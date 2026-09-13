@extends('layouts.public')
@section('title', 'Candidature reçue')

@section('content')
<section class="max-w-xl mx-auto px-4 py-24 text-center">
    <div class="inline-grid place-items-center w-16 h-16 rounded-full bg-green-100 text-green-700 text-3xl mb-6">✓</div>
    <h1 class="text-3xl font-bold text-jci-900">Candidature bien reçue !</h1>
    <p class="mt-3 text-slate-600">
        Merci pour votre intérêt. Votre candidature a rejoint notre processus de recrutement ;
        un responsable vous contactera prochainement.
    </p>
    <a href="{{ route('home') }}" class="inline-block mt-8 bg-jci-900 text-white font-semibold px-6 py-3 rounded-lg hover:bg-jci-700">
        Retour à l'accueil
    </a>
</section>
@endsection
