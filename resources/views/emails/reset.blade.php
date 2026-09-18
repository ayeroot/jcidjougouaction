<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#155e75;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
            <div style="font-size:20px;font-weight:800;">JCI Djougou Action</div>
            <div style="font-size:13px;color:#cffafe;">Réinitialisation du mot de passe</div>
        </div>
        <div style="background:#fff;padding:24px;border-radius:0 0 12px 12px;border:1px solid #e2e8f0;border-top:0;">
            <p>Bonjour {{ $user->name }},</p>
            <p>Vous recevez cet email car une demande de réinitialisation de mot de passe
               a été faite pour votre compte sur la plateforme
               <strong>JCI Djougou Action</strong>.</p>

            <p style="text-align:center;margin:24px 0;">
                <a href="{{ $url }}" style="background:#0891b2;color:#fff;text-decoration:none;
                   padding:12px 24px;border-radius:8px;font-weight:bold;display:inline-block;">
                   Réinitialiser le mot de passe
                </a>
            </p>

            <p style="font-size:13px;color:#64748b;">Ce lien expirera dans {{ $minutes }} minutes.</p>
            <p style="font-size:13px;color:#64748b;">Si vous n'êtes pas à l'origine de cette demande,
               aucune action n'est requise.</p>

            <p style="font-size:12px;color:#94a3b8;word-break:break-all;border-top:1px solid #eef2f7;padding-top:12px;">
               Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>{{ $url }}</p>
        </div>
        <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;">
            Cordialement,<br>L'équipe JCI Djougou Action — © {{ date('Y') }}
        </p>
    </div>
</body>
</html>
