<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#155e75;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
            <div style="font-size:20px;font-weight:800;">JCI Djougou Action</div>
            <div style="font-size:13px;color:#cffafe;">Espace de gestion</div>
        </div>
        <div style="background:#fff;padding:24px;border-radius:0 0 12px 12px;border:1px solid #e2e8f0;border-top:0;">
            <p>Bonjour {{ $user->name }},</p>
            <p>Un compte vient d'être créé pour vous sur la plateforme de gestion de
               <strong>JCI Djougou Action</strong>, avec le rôle
               <strong>{{ $roleLibelle }}</strong>.</p>

            <div style="background:#ecfeff;border:1px solid #a5f3fc;border-radius:8px;padding:16px;margin:18px 0;">
                <div style="font-size:13px;color:#0e7490;margin-bottom:6px;">Vos identifiants</div>
                <div><strong>Email :</strong> {{ $user->email }}</div>
                <div><strong>Mot de passe provisoire :</strong> {{ $motDePasse }}</div>
            </div>

            <p style="font-size:14px;">Pour votre sécurité, veuillez modifier ce mot de passe
               après votre première connexion.</p>

            <p style="text-align:center;margin:24px 0;">
                <a href="{{ $url }}" style="background:#0891b2;color:#fff;text-decoration:none;
                   padding:12px 24px;border-radius:8px;font-weight:bold;display:inline-block;">
                   Se connecter
                </a>
            </p>

            <p style="font-size:12px;color:#94a3b8;">Si vous n'êtes pas concerné par ce message,
               vous pouvez l'ignorer.</p>
        </div>
        <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;">
            © {{ date('Y') }} JCI Djougou Action
        </p>
    </div>
</body>
</html>
