@extends('layouts.storefront')

@php($roleLabels = ['admin' => 'Admin', 'curator' => 'Kurator', 'customer_service' => 'Customer Service', 'artisan' => 'Perajin', 'customer' => 'Customer'])

@section('title', 'Profil ' . $user->name . ' — KaryaLokal.id')

@section('content')
<section class="shell profile-page">
    <a class="muted" style="font-size:11px" href="{{ match($user->role) { 'admin' => route('admin.dashboard'), 'curator' => route('curator.dashboard'), 'customer_service' => route('support.dashboard'), 'artisan' => route('artisan.dashboard'), default => route('home') } }}">← Kembali ke dashboard</a>
    <div class="profile-heading">
        <div>
            <div class="eyebrow">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</div>
            <h1 class="page-title">Profil akun</h1>
        </div>
        <span class="verified">{{ ucfirst($user->status_akun) }}</span>
    </div>

    @if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('PUT')
        <section class="profile-section">
            <h2 class="product-name">Informasi akun</h2>
            <div class="profile-photo-editor">
                <x-profile-avatar :user="$user" :size="76" />
                <label class="field">Foto profil
                    <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                    <span class="muted" style="font-size:9px;font-weight:400">JPG, PNG, atau WebP, maksimal 5 MB.</span>
                    @error('profile_photo')<span class="error">{{ $message }}</span>@enderror
                </label>
            </div>
            <div class="profile-fields">
                <label class="field">Nama<input name="name" value="{{ old('name', $user->name) }}" required maxlength="255"></label>
                <label class="field">Email<input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"></label>
                <label class="field">Nomor HP<input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30"></label>
            </div>
            @foreach(['name', 'email', 'phone'] as $field)
                @error($field)<span class="error">{{ $message }}</span>@enderror
            @endforeach
        </section>

        @if($user->isArtisan())
            <section class="profile-section">
                <h2 class="product-name">Profil perajin</h2>
                <div class="profile-fields">
                    <label class="field">Spesialisasi<input name="specialty" value="{{ old('specialty', $user->artisanProfile?->specialty) }}" maxlength="150"></label>
                    <label class="field">Tahun mulai berkarya<input type="number" name="started_year" value="{{ old('started_year', $user->artisanProfile?->started_year) }}" min="1900" max="{{ now()->year }}"></label>
                    <label class="field">Kota<input name="city" value="{{ old('city', $user->artisanProfile?->city) }}" maxlength="100"></label>
                    <label class="field">Provinsi<input name="province" value="{{ old('province', $user->artisanProfile?->province) }}" maxlength="100"></label>
                    <label class="field field-wide">Bio<textarea name="bio" rows="3" maxlength="2000">{{ old('bio', $user->artisanProfile?->bio) }}</textarea></label>
                    <label class="field field-wide">Cerita perajin<textarea name="maker_story" rows="5" maxlength="5000">{{ old('maker_story', $user->artisanProfile?->maker_story) }}</textarea></label>
                </div>
                @foreach(['specialty', 'started_year', 'city', 'province', 'bio', 'maker_story'] as $field)
                    @error($field)<span class="error">{{ $message }}</span>@enderror
                @endforeach
                <p class="muted" style="font-size:10px;margin-bottom:0">Status verifikasi perajin hanya dapat diubah oleh kurator.</p>
            </section>
        @endif

        <details class="profile-section password-section">
            <summary>Ubah password</summary>
            <div class="profile-fields" style="margin-top:14px">
                <label class="field field-wide">Password saat ini<input type="password" name="current_password" autocomplete="current-password"></label>
                <label class="field">Password baru<input type="password" name="password" autocomplete="new-password" minlength="8"></label>
                <label class="field">Konfirmasi password<input type="password" name="password_confirmation" autocomplete="new-password" minlength="8"></label>
            </div>
            @foreach(['current_password', 'password'] as $field)
                @error($field)<span class="error">{{ $message }}</span>@enderror
            @endforeach
        </details>

        <button class="primary" type="submit">Simpan profil</button>
    </form>
</section>
@endsection

@push('styles')
<style>
    .profile-page{max-width:900px;padding:38px 0 52px}
    .profile-heading{display:flex;justify-content:space-between;align-items:end;gap:18px;margin:20px 0}
    .profile-heading .page-title{margin-bottom:0}
    .profile-form{display:grid;gap:14px}
    .profile-section{padding:18px;background:var(--white);border:1px solid var(--line)}
    .profile-photo-editor{display:flex;align-items:center;gap:15px;margin:0 0 16px;padding-bottom:16px;border-bottom:1px solid var(--line)}
    .profile-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .field{display:grid;gap:6px;color:var(--muted);font-size:10px;font-weight:700}
    .field input,.field textarea{width:100%;min-width:0;border:1px solid var(--line);padding:10px;background:var(--white);color:var(--ink);font:12px var(--sans)}
    .field-wide{grid-column:1/-1}
    .password-section summary{cursor:pointer;font-size:12px;font-weight:700}
    .notice{padding:10px;background:#e6efdf;color:var(--green);font-size:11px}
    .error{display:block;margin-top:6px;color:#a33;font-size:10px}
    @media(max-width:600px){.profile-fields{grid-template-columns:1fr}.field-wide{grid-column:auto}}
    @media(max-width:420px){.profile-photo-editor{align-items:flex-start;flex-direction:column}}
</style>
@endpush