<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f5ef">
    <title>Masuk — KaryaLokal.id</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--paper:#f7f5ef;--ink:#18352d;--muted:#69756d;--green:#245a46;--orange:#d5633a;--line:#deded4;--white:#fffefa;--serif:'Playfair Display',Georgia,serif;--sans:'DM Sans',sans-serif}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--paper);color:var(--ink);font-family:var(--sans);display:grid;grid-template-columns:1fr 1fr}a{color:inherit;text-decoration:none}.visual{position:relative;min-height:100vh;background:#33463b;overflow:hidden}.visual img{width:100%;height:100%;position:absolute;inset:0;object-fit:cover;opacity:.82}.visual:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(14,37,28,.05),rgba(14,37,28,.76))}.visual-copy{position:absolute;z-index:1;left:clamp(28px,7vw,100px);right:clamp(25px,6vw,75px);bottom:clamp(34px,8vh,90px);color:white}.visual-copy span{font-size:10px;letter-spacing:2px;text-transform:uppercase;color:#e8c481;font-weight:700}.visual-copy h2{font:500 clamp(32px,4vw,52px)/1.15 var(--serif);letter-spacing:0;max-width:520px;margin:16px 0}.visual-copy p{font-size:13px;line-height:1.8;color:#e4e9e1;max-width:380px}.panel{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:40px}.form-wrap{width:min(390px,100%)}.brand{font:700 24px var(--serif);letter-spacing:0;color:var(--ink)}.brand span{color:var(--orange)}.back{display:inline-flex;gap:8px;align-items:center;margin-bottom:48px;color:var(--muted);font-size:12px}.back:hover{color:var(--orange)}.back svg{width:15px}.eyebrow{margin-top:52px;color:var(--orange);font-size:10px;letter-spacing:1.8px;font-weight:700;text-transform:uppercase}.form-wrap h1{font:600 37px/1.2 var(--serif);letter-spacing:0;margin:11px 0}.intro{font-size:13px;color:var(--muted);line-height:1.7;margin-bottom:29px}.field{margin-bottom:17px}.field label{display:block;font-size:11px;font-weight:700;margin-bottom:7px}.field input{width:100%;height:46px;border:1px solid var(--line);background:var(--white);padding:0 13px;color:var(--ink);font:13px var(--sans);outline:none}.field input:focus{border-color:var(--green);box-shadow:0 0 0 2px rgba(36,90,70,.12)}.password-wrap{position:relative}.password-wrap input{padding-right:50px}.toggle{position:absolute;right:0;top:0;height:46px;width:44px;border:0;background:transparent;color:var(--muted);cursor:pointer}.toggle svg{width:18px}.remember{display:flex;align-items:center;gap:8px;font-size:11px;color:var(--muted);margin:5px 0 23px}.remember input{accent-color:var(--green)}.submit{width:100%;height:47px;background:var(--green);color:white;border:1px solid var(--green);font:700 12px var(--sans);cursor:pointer}.submit:hover{background:#194533}.error{background:#fff1eb;color:#a43e22;border:1px solid #efc8b7;padding:11px 13px;font-size:12px;margin-bottom:18px}.foot{border-top:1px solid var(--line);margin-top:26px;padding-top:17px;color:var(--muted);font-size:10px;line-height:1.7}.foot a{color:var(--green);font-weight:700}.copyright{margin-top:27px;font-size:10px;color:#89938a}@media(max-width:760px){body{display:block}.visual{display:none}.panel{min-height:100vh;padding:28px 24px}.back{margin-bottom:55px}.eyebrow{margin-top:45px}.form-wrap h1{font-size:34px}}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}
    </style>
    <style>.brand{display:block;width:min(220px,100%);margin-bottom:12px}.brand img{display:block;width:100%;height:auto;max-height:55px}</style>
</head>
<body>
    <aside class="visual"><img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=1200&q=85" alt="Perajin sedang membentuk karya keramik"><div class="visual-copy"><span>Temukan cerita di balik setiap karya</span><h2>Benda istimewa, dibuat oleh tangan-tangan istimewa.</h2><p>Kenali para perajin dan cerita yang mereka titipkan pada setiap karya.</p></div></aside>
    <main class="panel">
        <div class="form-wrap">
            <a class="back" href="{{ route('home') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M19 12H5m6 7-7-7 7-7"/></svg>Kembali ke KaryaLokal</a>
            <x-brand-lockup :href="route('home')" class="auth-brand" />
            <div class="eyebrow">Selamat datang kembali</div>
            <h1>Masuk ke akunmu.</h1>
            <p class="intro">Lanjutkan menjelajahi karya dan cerita dari para perajin lokal.</p>
            @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field"><label for="email">Alamat email</label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com" required autofocus></div>
                <div class="field"><label for="password">Password</label><div class="password-wrap"><input id="password" type="password" name="password" autocomplete="current-password" placeholder="Masukkan password" required><button class="toggle" type="button" id="togglePassword" aria-label="Tampilkan password"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div></div>
                <label class="remember"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
                <button class="submit" type="submit">Masuk</button>
            </form>
            <div class="foot">Butuh bantuan dengan akunmu? Hubungi tim KaryaLokal untuk mendapat bantuan.</div>
            <div class="copyright">© {{ date('Y') }} KaryaLokal.id</div>
        </div>
    </main>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const password = document.getElementById('password');
            const visible = password.type === 'password';
            password.type = visible ? 'text' : 'password';
            this.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        });
    </script>
</body>
</html>
