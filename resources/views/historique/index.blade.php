@extends('layouts.app')
@section('title', 'Historique des activités')
@section('content')
@php
    $actionLabel = ['CREATE'=>'Création','UPDATE'=>'Modification','DELETE'=>'Suppression','LOGIN'=>'Connexion','LOGIN_ECHEC'=>'Connexion échouée','ROLE_PERMISSIONS'=>'Permissions du rôle','ROLE_RESET'=>'Rôle réinitialisé','USER_PRIVILEGES'=>'Privilèges modifiés','EMAIL_CHANGE'=>'Email modifié'];
    $actionBadge = ['CREATE'=>'bg-green-100 text-green-700','UPDATE'=>'bg-amber-100 text-amber-700','DELETE'=>'bg-red-100 text-red-700'];
    $roleLabels  = \App\Http\Controllers\Admin\UserController::ROLES;
    $entites     = \App\Http\Controllers\HistoriqueController::ENTITES;
@endphp
<h1 class="text-2xl font-bold text-jci-900 mb-1">Historique des activités</h1>
<p class="text-slate-500 text-sm mb-5">Journal d'audit : toutes les créations, modifications et suppressions sont tracées (les lectures ne le sont pas). Généré côté serveur.</p>

<div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 text-left">
            <tr>
                <th class="px-4 py-3">Date &amp; heure</th>
                <th class="px-4 py-3">Utilisateur</th>
                <th class="px-4 py-3">Rôle</th>
                <th class="px-4 py-3">Action</th>
                <th class="px-4 py-3">Entité</th>
                <th class="px-4 py-3">Détails</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse ($logs as $log)
                <tr class="align-top">
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $log->user?->name ?? 'Système' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $roleLabels[$log->role] ?? $log->role ?? '—' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded {{ $actionBadge[$log->action] ?? '' }}">{{ $actionLabel[$log->action] ?? $log->action }}</span></td>
                    <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $entites[$log->auditable_type] ?? class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                    <td class="px-4 py-3">
                        @if (in_array($log->action, ['UPDATE','ROLE_PERMISSIONS','USER_PRIVILEGES','EMAIL_CHANGE']) && $log->new_values)
                            <details class="text-xs text-slate-500">
                                <summary class="cursor-pointer text-jci-600">{{ count($log->new_values) }} champ(s) modifié(s)</summary>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($log->new_values as $champ => $val)
                                        <li><span class="font-medium">{{ $champ }}</span> :
                                            <span class="text-red-500 line-through">{{ \Illuminate\Support\Str::limit((string)($log->old_values[$champ] ?? '—'), 40) }}</span>
                                            →
                                            <span class="text-green-600">{{ \Illuminate\Support\Str::limit((string) $val, 40) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @elseif ($log->action === 'CREATE')
                            <span class="text-xs text-slate-400">Nouvel enregistrement</span>
                        @elseif ($log->action === 'DELETE')
                            <span class="text-xs text-slate-400">Enregistrement supprimé</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Aucune activité enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
