@php
    $tabs = [
        ['finances.index',"Vue d'ensemble"],
        ['finances.cotisations','Cotisations'],
        ['finances.contributions','Contributions'],
        ['finances.depenses','Dépenses'],
    ];
@endphp
<div class="flex flex-wrap gap-2 mb-6 text-sm">
    @foreach ($tabs as [$route,$label])
        <a href="{{ route($route) }}" class="px-4 py-2 rounded-lg border {{ request()->routeIs($route) ? 'bg-jci-900 text-white' : 'bg-white hover:bg-slate-50' }}">{{ $label }}</a>
    @endforeach
</div>
