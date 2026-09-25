<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#155e75;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
            <div style="font-size:20px;font-weight:800;">JCI Djougou Action</div>
            <div style="font-size:13px;color:#cffafe;">Alerte de sécurité</div>
        </div>
        <div style="background:#fff;padding:24px;border-radius:0 0 12px 12px;border:1px solid #e2e8f0;border-top:0;">
            <p>Bonjour {{ $user->name }},</p>
            @if ($confirme)
                <p>L'adresse email de votre compte a été remplacée par <strong>{{ $nouvelEmail }}</strong>.
                   Cette adresse-ci ne sera plus utilisée pour vous connecter.</p>
            @else
                <p>Une demande a été faite pour remplacer l'adresse email de votre compte par
                   <strong>{{ $nouvelEmail }}</strong>. Le changement ne sera effectif qu'après confirmation
                   depuis cette nouvelle adresse.</p>
            @endif
            <p style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 16px;border-radius:8px;">
                <strong>Ce n'est pas vous ?</strong> Changez immédiatement votre mot de passe et prévenez le Président local.
            </p>
        </div>
        <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;">© {{ date('Y') }} JCI Djougou Action</p>
    </div>
</body>
</html>
