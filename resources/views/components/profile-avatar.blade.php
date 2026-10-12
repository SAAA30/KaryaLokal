@props(['user', 'size' => 34])
@php($avatarSizeClass = $size >= 60 ? 'profile-avatar-large' : '')

<span class="profile-avatar {{ $avatarSizeClass }}" aria-label="Foto profil {{ $user->name }}">
    @if($user->profile_photo_path)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($user->profile_photo_path) }}" alt="Foto profil {{ $user->name }}">
    @else
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <circle cx="12" cy="8" r="3.5"/>
            <path d="M5 20c.5-3.5 3.1-5.5 7-5.5s6.5 2 7 5.5"/>
        </svg>
    @endif
</span>

@once
    <style>
        .profile-avatar{display:inline-grid;place-items:center;flex:none;width:34px;height:34px;overflow:hidden;border:1px solid var(--line);border-radius:50%;background:#eeede5;color:var(--green);vertical-align:middle}
        .profile-avatar-large{width:76px;height:76px}
        .profile-avatar img{width:100%;height:100%;object-fit:cover}
        .profile-avatar svg{width:56%;height:56%}
        .profile-link{display:inline-flex;align-items:center;gap:7px}
    </style>
@endonce