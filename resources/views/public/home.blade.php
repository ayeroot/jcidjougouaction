@extends('layouts.public')
@section('title', 'Accueil — JCI Djougou Action')

@section('content')
@php
    $v = $vitrine ?? [];
    $heroTitre = $v['vitrine_hero_titre'] ?? 'Développer le leadership des jeunes citoyens de Djougou.';
    $heroSous  = $v['vitrine_hero_sous_titre'] ?? null;
    $showProjets = ($v['vitrine_section_projets'] ?? '1') === '1';
    $showPartenaires = ($v['vitrine_section_partenaires'] ?? '1') === '1';
    $showInscription = ($v['vitrine_section_inscription'] ?? '1') === '1';
@endphp

{{-- Hero --}}
<section class="bg-gradient-to-br from-jci-900 to-jci-700 text-white">
    <div class="max-w-6xl mx-auto px-4 py-20 md:py-28">
        <p class="uppercase tracking-widest text-jci-100 text-sm mb-3">Jeune Chambre Internationale</p>
        <h1 class="text-4xl md:text-5xl font-black leading-tight max-w-3xl">
            {{ $heroTitre }}
        </h1>
        <p class="mt-5 text-lg text-slate-200 max-w-2xl">
            @if ($heroSous)
                {{ $heroSous }}
            @elseif ($mandat && $mandat->theme)
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

@if (!empty($v['vitrine_mission']))
<section class="max-w-6xl mx-auto px-4 pt-14">
    <p class="text-lg text-slate-700 max-w-3xl">{{ $v['vitrine_mission'] }}</p>
</section>
@endif

{{-- Projets --}}
@if ($showProjets)
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
@endif

{{-- Partenaires --}}
@if ($showPartenaires)
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
@endif

{{-- Appel à l'action --}}
@if ($showInscription)
<section class="max-w-6xl mx-auto px-4 py-16 text-center">
    <h2 class="text-2xl md:text-3xl font-bold text-jci-900">Envie de vous engager ?</h2>
    <p class="mt-3 text-slate-600 max-w-xl mx-auto">
        Remplissez le formulaire d'inscription : votre candidature rejoint directement notre processus de recrutement.
    </p>
    <a href="{{ route('inscription.create') }}" class="inline-block mt-6 bg-jci-900 text-white font-semibold px-6 py-3 rounded-lg hover:bg-jci-700">
        Déposer ma candidature
    </a>
</section>
@endif

{{-- Contact (si renseigné) --}}
@if (!empty($v['vitrine_contact_ville']) || !empty($v['vitrine_contact_email']) || !empty($v['vitrine_contact_tel']))
<section class="bg-jci-900 text-white">
    <div class="max-w-6xl mx-auto px-4 py-12 grid sm:grid-cols-3 gap-6 text-sm">
        @if (!empty($v['vitrine_contact_ville']))<div><div class="text-jci-100 uppercase text-xs tracking-wide mb-1">Adresse</div>{{ $v['vitrine_contact_ville'] }}</div>@endif
        @if (!empty($v['vitrine_contact_email']))<div><div class="text-jci-100 uppercase text-xs tracking-wide mb-1">Email</div>{{ $v['vitrine_contact_email'] }}</div>@endif
        @if (!empty($v['vitrine_contact_tel']))<div><div class="text-jci-100 uppercase text-xs tracking-wide mb-1">Téléphone</div>{{ $v['vitrine_contact_tel'] }}</div>@endif
    </div>
</section>
@endif

@endsection
