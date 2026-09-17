<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste de présence — {{ $formation->titre }}</title>
    <style>
        * { font-family: Arial, Helvetica, sans-serif; }
        body { color: #1f2937; margin: 32px; }
        .head { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:3px solid #155e75; padding-bottom:12px; }
        .brand { font-weight:800; color:#155e75; font-size:20px; }
        h1 { color:#155e75; font-size:18px; margin:18px 0 4px; }
        .meta { font-size:13px; color:#555; margin-bottom:16px; }
        .meta b { color:#111; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        th, td { border:1px solid #999; padding:8px 10px; font-size:13px; text-align:left; }
        th { background:#155e75; color:#fff; }
        td.sign { height:34px; }
        .noprint { margin-bottom:16px; }
        button { background:#155e75; color:#fff; border:0; padding:8px 16px; border-radius:6px; cursor:pointer; }
        @media print { .noprint { display:none; } body { margin:0; } }
    </style>
</head>
<body>
    <div class="noprint"><button onclick="window.print()">🖨️ Imprimer</button></div>

    <div class="head">
        <div class="brand">JCI Djougou Action</div>
        <div style="text-align:right; font-size:12px; color:#555;">Liste de présence</div>
    </div>

    <h1>{{ $formation->titre }}</h1>
    <div class="meta">
        <b>Thème :</b> {{ $formation->theme ?: '—' }} &nbsp;|&nbsp;
        <b>Date :</b> {{ $formation->date_formation?->format('d/m/Y H:i') ?: '—' }} &nbsp;|&nbsp;
        <b>Lieu :</b> {{ $formation->lieu ?: '—' }} &nbsp;|&nbsp;
        <b>Formateur :</b> {{ $formation->formateur?->nom ?: '—' }}
    </div>

    <table>
        <thead>
            <tr><th style="width:40px;">N°</th><th>Nom et prénom</th><th style="width:130px;">Téléphone</th><th style="width:180px;">Signature</th></tr>
        </thead>
        <tbody>
            @forelse ($eligibles as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->nom_complet }}</td>
                    <td>{{ $p->telephone }}</td>
                    <td class="sign"></td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#888;">Aucun postulant.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
