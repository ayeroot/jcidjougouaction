{{-- Logo JCI : image choisie dans « Site vitrine » (ou logo du mandat actif), sinon badge « JCI ».
     taille : classes Tailwind (ex. « w-9 h-9 ») · fond du badge de secours : « clair » ou « fonce » --}}
@props(['taille' => 'w-9 h-9', 'fond' => 'clair'])
@php $logoUrl = \App\Support\Logo::url(); @endphp

@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="Logo JCI Djougou Action"
         {{ $attributes->merge(['class' => "$taille object-contain rounded bg-white shrink-0"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$taille inline-grid place-items-center rounded font-black text-sm shrink-0 "
        .($fond === 'fonce' ? 'bg-jci-900 text-white' : 'bg-white text-jci-900')]) }}>JCI</span>
@endif
