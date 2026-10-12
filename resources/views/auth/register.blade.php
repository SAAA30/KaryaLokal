<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f5ef">
    <title>Buat Akun — KaryaLokal.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--paper:#f7f5ef;--ink:#18352d;--muted:#69756d;--green:#245a46;--orange:#d5633a;--line:#deded4;--white:#fffefa;--serif:'Playfair Display',Georgia,serif;--sans:'DM Sans',sans-serif}*{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--paper);color:var(--ink);font-family:var(--sans);display:grid;grid-template-columns:.8fr 1.2fr}a{color:inherit;text-decoration:none}.visual{position:relative;min-height:100vh;background:#33463b;overflow:hidden}.visual img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.82}.visual:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(14,37,28,.05),rgba(14,37,28,.76))}.visual-copy{position:absolute;z-index:1;left:clamp(28px,6vw,82px);right:28px;bottom:8vh;color:white}.visual-copy span{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#e8c481;font-weight:700}.visual-copy h2{font:500 clamp(30px,3.6vw,48px)/1.15 var(--serif);letter-spacing:0;margin:15px 0}.panel{display:flex;align-items:center;justify-content:center;padding:40px 25px}.form-wrap{width:min(520px,100%)}.brand{font:700 23px var(--serif)}.brand span{color:var(--orange)}.back{display:block;margin-bottom:34px;color:var(--muted);font-size:11px}.eyebrow{margin-top:35px;color:var(--orange);font-size:10px;letter-spacing:1.8px;font-weight:700;text-transform:uppercase}.form-wrap h1{font:600 35px/1.2 var(--serif);letter-spacing:0;margin:9px 0}.intro{font-size:12px;color:var(--muted);margin:0 0 19px}.fields{display:grid;grid-template-columns:1fr 1fr;gap:12px}.field{display:grid;gap:6px;margin-bottom:12px}.field label{font-size:10px;font-weight:700}.field input{width:100%;height:42px;border:1px solid var(--line);background:var(--white);padding:0 11px;color:var(--ink);font:12px var(--sans);outline:none}.field input:focus{border-color:var(--green);box-shadow:0 0 0 2px rgba(36,90,70,.12)}.role-options{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin:13px 0}.role-option{display:flex;gap:8px;align-items:center;padding:11px;border:1px solid var(--line);background:var(--white);font-size:11px;cursor:pointer}.role-option:has(input:checked){border-color:var(--green);background:#edf1e7}.role-option input{accent-color:var(--green)}.submit{width:100%;height:45px;background:var(--green);color:white;border:1px solid var(--green);font:700 12px var(--sans);cursor:pointer}.submit:hover{background:#194533}.error{background:#fff1eb;color:#a43e22;border:1px solid #efc8b7;padding:10px 12px;font-size:11px;margin-bottom:13px}.foot{border-top:1px solid var(--line);margin-top:18px;padding-top:14px;color:var(--muted);font-size:10px}.foot a{color:var(--green);font-weight:700}@media(max-width:760px){body{display:block}.visual{display:none}.panel{min-height:100vh;padding:26px 20px}.fields{grid-template-columns:1fr}.eyebrow{margin-top:30px}}
        .brand{display:block;width:min(220px,100%);margin-bottom:12px}.brand img{display:block;width:100%;height:auto;max-height:55px}
    </style>
</head>
<body>
    <aside class="visual"><img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=1200&q=85" alt="Perajin membentuk karya keramik"><div class="visual-copy"><span>Temukan cerita di balik setiap karya</span><h2>Kenali perajin, dukung keterampilan lokal.</h2></div></aside>
    <main class="panel"><div class="form-wrap">
        <a class="back" href="{{ route('home') }}">← Kembali ke KaryaLokal</a>
        <x-brand-lockup :href="route('home')" class="auth-brand" />
        <div class="eyebrow">Bergabung dengan komunitas</div><h1>Buat akun KaryaLokal.</h1><p class="intro">Mulai menjelajahi karya atau bagikan hasil karyamu.</p>
        @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="fields"><div class="field"><label for="name">Nama lengkap</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name"></div><div class="field"><label for="phone">Nomor HP</label><input id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel"></div><div class="field" style="grid-column:1/-1"><label for="email">Alamat email</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></div><div class="field"><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" required autocomplete="new-password"></div><div class="field"><label for="password_confirmation">Ulangi password</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"></div></div>
            <div class="eyebrow" style="margin-top:8px">Saya ingin bergabung sebagai</div>
            <div class="role-options"><label class="role-option"><input type="radio" name="role" value="customer" @checked(old('role', request()->query('role', 'customer')) === 'customer') required> Pembeli</label><label class="role-option"><input type="radio" name="role" value="artisan" @checked(old('role', request()->query('role', 'customer')) === 'artisan')> Perajin</label></div>
            <div id="artisanLocation" class="fields" hidden><div class="field"><label for="city">Kota asal</label><input id="city" name="city" value="{{ old('city') }}" maxlength="100"></div><div class="field"><label for="province">Provinsi</label><input id="province" name="province" value="{{ old('province') }}" maxlength="100"></div></div>
            <button class="submit" type="submit">Buat akun</button>
        </form>
        <div class="foot">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
    </div></main>
    <script>
        const artisanRole = document.querySelector('input[name="role"][value="artisan"]');
        const artisanLocation = document.getElementById('artisanLocation');
        function updateArtisanFields() { artisanLocation.hidden = !artisanRole.checked; }
        document.querySelectorAll('input[name="role"]').forEach((input) => input.addEventListener('change', updateArtisanFields));
        updateArtisanFields();
    </script>
</body>
</html>
