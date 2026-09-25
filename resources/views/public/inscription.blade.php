@extends('layouts.public')
@section('title', 'Inscription — JCI Djougou Action')

@section('content')
<section class="max-w-2xl mx-auto px-4 py-14">
    <h1 class="text-3xl font-bold text-jci-900">Formulaire d'inscription</h1>
    <p class="text-slate-600 mt-2">Devenez postulant de JCI Djougou Action. Les champs marqués d'un * sont obligatoires.</p>

    @if ($errors->any())
        <div class="mt-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inscription.store') }}" class="mt-8 space-y-5 bg-white border rounded-xl p-6">
        @csrf
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium mb-1">Nom *</label>
                <input name="nom" value="{{ old('nom') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Prénom *</label>
                <input name="prenom" value="{{ old('prenom') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date de naissance</label>
                <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Sexe</label>
                <select name="sexe" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                    <option value="">—</option>
                    <option value="M" @selected(old('sexe')==='M')>Masculin</option>
                    <option value="F" @selected(old('sexe')==='F')>Féminin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Téléphone *</label>
                <input name="telephone" value="{{ old('telephone') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Ville / Quartier</label>
                <input name="ville" value="{{ old('ville') }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium mb-1">Motivation</label>
                <textarea name="motivation" rows="4" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">{{ old('motivation') }}</textarea>
            </div>
        </div>

        {{-- Anti-robot (pot de miel) : champ invisible pour un humain, rempli par les robots. --}}
        <div class="hidden" aria-hidden="true">
            <label>Site web <input type="text" name="site_web" value="" tabindex="-1" autocomplete="off"></label>
        </div>

        {{-- Consentement (loi n° 2017-20 portant Code du numérique en République du Bénin) --}}
        <label class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" name="consentement" value="1" required class="mt-1" @checked(old('consentement'))>
            <span>
                J'accepte que JCI Djougou Action enregistre ces informations pour traiter ma candidature.
                Elles ne sont accessibles qu'au bureau chargé du recrutement et ne sont pas transmises à des tiers.
                Je peux demander leur consultation, leur correction ou leur suppression en écrivant à l'OLM.
            </span>
        </label>
        @error('consentement') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <button class="w-full bg-jci-900 text-white font-semibold py-3 rounded-lg hover:bg-jci-700">
            Envoyer ma candidature
        </button>
    </form>
</section>
@endsection
