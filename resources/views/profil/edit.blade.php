@extends('layouts.app')
@section('title', 'Mon profil')
@section('content')
@php $membre = $user->membre; @endphp

<h1 class="text-2xl font-bold text-jci-900 mb-1">Mon profil</h1>
<p class="text-slate-500 text-sm mb-6">Modifiez vos informations, votre adresse email et votre mot de passe.</p>

@if ($errors->any())
    <div class="mb-5 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid lg:grid-cols-2 gap-5">
    <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="bg-white border rounded-xl p-6">
        @csrf @method('PUT')
        <h2 class="font-semibold text-jci-900 mb-3">Informations</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nom affiché *</label>
                <input name="name" value="{{ old('name', $user->name) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="w-full border rounded-lg px-3 py-2 bg-slate-50 text-slate-500">
                <p class="text-xs text-slate-400 mt-1">Pour la changer, utilisez le bloc « Changer mon adresse email ».</p>
            </div>
            @if ($membre)
                <div>
                    <label class="block text-sm font-medium mb-1">Téléphone</label>
                    <input name="telephone" value="{{ old('telephone', $membre->telephone) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ville</label>
                    <input name="ville" value="{{ old('ville', $membre->ville) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Adresse</label>
                    <input name="adresse" value="{{ old('adresse', $membre->adresse) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Photo</label>
                    <img src="{{ $membre->photo_url }}" alt="" class="w-16 h-16 rounded-full object-cover border mb-2">
                    <input type="file" name="photo" accept="image/*" class="w-full text-sm border rounded-lg px-3 py-2">
                </div>
            @endif
        </div>
        <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Enregistrer</button>
    </form>

    <div class="space-y-5">
    <form method="POST" action="{{ route('profil.email') }}" class="bg-white border rounded-xl p-6 h-fit">
        @csrf @method('PUT')
        <h2 class="font-semibold text-jci-900 mb-1">Changer mon adresse email</h2>
        <p class="text-xs text-slate-500 mb-4">Un lien de confirmation sera envoyé à la nouvelle adresse. Le changement n'a lieu
            qu'après avoir cliqué dessus ; votre adresse actuelle est prévenue.</p>

        @if ($user->email_en_attente && $user->email_token_expire_at?->isFuture())
            <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 px-4 py-2 text-sm">
                En attente de confirmation : <strong>{{ $user->email_en_attente }}</strong>
                (lien valable jusqu'au {{ $user->email_token_expire_at->format('d/m/Y à H:i') }}).
            </div>
        @endif

        @if ($errors->email->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-2 text-sm">{{ $errors->email->first() }}</div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nouvelle adresse email</label>
                <input type="email" name="nouvel_email" value="{{ old('nouvel_email') }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Mot de passe actuel</label>
                <input type="password" name="actuel_email" required autocomplete="current-password" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
        </div>
        <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Envoyer le lien de confirmation</button>
    </form>

    <form method="POST" action="{{ route('profil.password') }}" class="bg-white border rounded-xl p-6 h-fit">
        @csrf @method('PUT')
        <h2 class="font-semibold text-jci-900 mb-3">Changer mon mot de passe</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Mot de passe actuel</label>
                <input type="password" name="actuel" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" required minlength="10" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
        <p class="text-xs text-slate-500 mt-1">10 caractères minimum, avec des lettres et des chiffres.</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirmer</label>
                <input type="password" name="password_confirmation" required minlength="10" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-jci-600 outline-none">
            </div>
        </div>
        <button class="mt-6 bg-jci-900 text-white font-semibold px-6 py-2.5 rounded-lg hover:bg-jci-700">Modifier le mot de passe</button>
    </form>
    </div>
</div>
@endsection
