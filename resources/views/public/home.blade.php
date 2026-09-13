@extends('layouts.public')
@section('title', 'Accueil — JCI Djougou Action')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-jci-900 to-jci-700 text-white">
    <div class="max-w-6xl mx-auto px-4 py-20 md:py-28">
        <p class="uppercase tracking-widest text-jci-100 text-sm mb-3">Jeune Chambre Internationale</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight max-w-3xl">
            Développer le leadership des jeunes citoyens de Djougou.
        </h1>
        <p class="mt-5 text-lg text-slate-200 max-w-2xl">
            @if ($mandat && $mandat->theme)
                Mandat {{ $mandat->annee }} — {{ $mandat->theme }}.
            @else
                Formation, projets d'impact et engagement citoyen au cœur de la Donga.
            @endif
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('inscription.create') }}" class="bg-white text-jci-900 font-semibold px-6 py-3 rounded-lg hover:bg-jci-100">
                Rejoindre l'organisation
            </a>
            <a href="#projets" class="border border-white/40 px-6 py-3 rounded-lg hover:bg-white/10">
                Découvrir nos projets
            </a>
        </div>
    </div>
</section>

{{-- Projets --}}
<section id="projets" class="max-w-6xl mx-auto px-4 py-16">
    <h2 class="text-2xl font-bold text-jci-900">Nos projets & actions</h2>
    <p class="text-slate-500 mt-1">Ce que notre organisation locale mène sur le terrain.</p>

    <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($projets as $projet)
            <article class="border rounded-xl p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold px-2 py-1 rounded bg-jci-100 text-jci-700">
                        {{ ['en_cours'=>'En cours','termine'=>'Terminé','a_venir'=>'À venir'][$projet->statut] ?? $projet->statut }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $projet->avancement }} %</span>
                </div>
                <h3 class="mt-3 font-semibold text-lg">{{ $projet->titre }}</h3>
                <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $projet->description }}</p>
            </article>
        @empty
            <p class="text-slate-500 col-span-full">Les projets seront bientôt publiés ici.</p>
        @endforelse
    </div>
</section>

{{-- Partenaires --}}
<section id="partenaires" class="bg-slate-50 border-y">
    <div class="max-w-6xl mx-auto px-4 py-16">
        <h2 class="text-2xl font-bold text-jci-900">Nos partenaires</h2>
        <div class="mt-8 flex flex-wrap gap-4">
            @forelse ($partenaires as $p)
                <div class="bg-white border rounded-lg px-5 py-4 min-w-[160px]">
                    <div class="font-semibold">{{ $p->nom }}</div>
                    <div class="text-xs text-slate-500">{{ $p->type }}</div>
                </div>
            @empty
                <p class="text-slate-500">Nos partenaires apparaîtront ici.</p>
            @endforelse
        </div>
    </div>
</section>

{{-- Appel à l'action --}}
<section class="max-w-6xl mx-auto px-4 py-16 text-center">
    <h2 class="text-2xl md:text-3xl font-bold text-jci-900">Envie de vous engager ?</h2>
    <p class="mt-3 text-slate-600 max-w-xl mx-auto">
        Remplissez le formulaire d'inscription : votre candidature rejoint directement notre processus de recrutement.
    </p>
    <a href="{{ route('inscription.create') }}" class="inline-block mt-6 bg-jci-900 text-white font-semibold px-6 py-3 rounded-lg hover:bg-jci-700">
        Déposer ma candidature
    </a>
</section>

@endsection
