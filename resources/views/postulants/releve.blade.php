<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Relevé de formations — {{ $postulant->nom_complet }}</title>
    <style>
        * { font-family: Arial, Helvetica, sans-serif; }
        body { color:#1f2937; margin:32px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #155e75; padding-bottom:12px; }
        .brand { font-weight:800; color:#155e75; font-size:20px; }
        h1 { color:#155e75; font-size:18px; margin:18px 0 4px; }
        .meta { font-size:13px; color:#555; margin-bottom:16px; }
        .meta b { color:#111; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { border:1px solid #999; padding:8px 10px; font-size:13px; text-align:left; }
        th { background:#155e75; color:#fff; }
        .ok { color:#15803d; font-weight:bold; }
        .no { color:#b91c1c; }
        .total { margin-top:14px; font-size:14px; }
        .noprint { margin-bottom:16px; }
        button { background:#155e75; color:#fff; border:0; padding:8px 16px; border-radius:6px; cursor:pointer; }
        @media print { .noprint { display:none; } body { margin:0; } }
    </style>
</head>
<body>
    <div class="noprint"><button onclick="window.print()">🖨️ Imprimer</button></div>

    <div class="head">
        <div class="brand">JCI Djougou Action</div>
        <div style="text-align:right; font-size:12px; color:#555;">Relevé individuel de formations</div>
    </div>

    <h1>{{ $postulant->nom_complet }}</h1>
    <div class="meta">
        <b>Téléphone :</b> {{ $postulant->telephone ?: '—' }} &nbsp;|&nbsp;
        <b>Email :</b> {{ $postulant->email ?: '—' }} &nbsp;|&nbsp;
        <b>Statut :</b> {{ ucfirst(str_replace('_',' ', $postulant->statut)) }}
    </div>

    <table>
        <thead>
            <tr><th style="width:40px;">N°</th><th>Formation</th><th style="width:110px;">Date</th><th style="width:110px;">Présence</th></tr>
        </thead>
        <tbody>
            @forelse ($postulant->formations as $i => $f)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $f->titre }}</td>
                    <td>{{ $f->date_formation?->format('d/m/Y') ?: '—' }}</td>
                    <td class="{{ $f->pivot->present ? 'ok' : 'no' }}">{{ $f->pivot->present ? 'Présent' : 'Absent' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#888;">Aucune formation enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="total"><b>Total de formations suivies (présent) :</b> {{ $postulant->nombreFormationsSuivies() }}</div>
</body>
</html>
