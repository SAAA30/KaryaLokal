<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f7f5ef">
    <title>@yield('title', 'KaryaLokal.id — Membeli karya, membawa pulang cerita')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--paper:#f7f5ef;--ink:#18352d;--muted:#69756d;--green:#245a46;--lime:#dce8bd;--orange:#d5633a;--line:#deded4;--white:#fffefa;--serif:'Playfair Display',Georgia,serif;--sans:'DM Sans',sans-serif}
        *{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font-family:var(--sans)}a{color:inherit;text-decoration:none}button,input,select{font:inherit}.shell{width:min(1180px,calc(100% - 40px));margin:auto}.announcement{padding:9px 12px;background:var(--green);color:#f5f2e9;text-align:center;font-size:10px;letter-spacing:.4px}.nav{height:72px;display:flex;align-items:center;gap:30px;border-bottom:1px solid var(--line)}.brand{font:700 23px var(--serif);letter-spacing:0;white-space:nowrap}.brand span{color:var(--orange)}.navlinks{display:flex;gap:22px;font-size:12px;color:#52635a}.navlinks a:hover{color:var(--orange)}.navright{display:flex;align-items:center;gap:14px;margin-left:auto}.search{display:flex;align-items:center;gap:8px;background:var(--white);border:1px solid var(--line);height:38px;padding:0 10px}.search input{width:205px;border:0;outline:0;background:transparent;font-size:11px}.signin{padding:10px 13px;border:1px solid var(--green);font-size:11px;font-weight:700}.signin:hover{background:var(--green);color:#fff}.footer{margin-top:60px;background:#eae9e0;padding:22px 0;color:var(--muted);font-size:10px}.footer-inner{display:flex;align-items:center;justify-content:space-between;gap:15px}.eyebrow{color:var(--orange);font-size:10px;letter-spacing:1.7px;text-transform:uppercase;font-weight:700}.page-title{font:600 38px/1.15 var(--serif);letter-spacing:0;margin:9px 0 10px}.muted{color:var(--muted)}.primary{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 15px;background:var(--green);color:white;border:1px solid var(--green);font-size:11px;font-weight:700;cursor:pointer}.primary:hover{background:#194533}.card-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:18px}.product-card{min-width:0}.product-photo{display:block;aspect-ratio:4/4.6;background:#e7e4d9;overflow:hidden}.product-photo img{width:100%;height:100%;object-fit:cover;transition:transform .35s}.product-photo:hover img{transform:scale(1.035)}.product-info{padding:11px 0}.artisan-caption{font-size:10px;color:var(--muted);margin-bottom:5px}.product-name{font:600 17px/1.3 var(--serif);margin:0 0 7px}.product-price{font-size:12px;font-weight:700}.product-origin{font-size:10px;color:var(--muted);margin-top:6px}.filters{display:flex;gap:10px;align-items:end;flex-wrap:wrap;padding:16px 0 20px;border-bottom:1px solid var(--line);margin-bottom:22px}.filters label{display:grid;gap:5px;color:var(--muted);font-size:10px}.filters input,.filters select{height:37px;border:1px solid var(--line);background:var(--white);padding:0 9px;color:var(--ink);font-size:11px}.filters .wide{width:190px}.pagination{margin-top:24px}.verified{display:inline-flex;align-items:center;gap:5px;padding:6px 9px;background:#e4eddb;color:var(--green);font-size:10px;font-weight:700}.story-section{padding:36px 0;border-bottom:1px solid var(--line)}@media(max-width:900px){.card-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.nav{gap:18px}.navlinks{gap:13px}.search input{width:150px}}@media(max-width:660px){.shell{width:calc(100% - 30px)}.nav{height:62px;gap:10px}.navlinks{display:none}.navright{gap:8px}.search{width:38px;padding:0;justify-content:center;background:transparent;border:0}.search input{display:none}.search:focus-within{position:absolute;left:15px;right:15px;top:43px;width:auto;justify-content:start;padding:0 10px;background:var(--white);border:1px solid var(--line);z-index:3}.search:focus-within input{display:block;width:100%}.brand{font-size:20px}.signin{padding:9px;font-size:10px}.card-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.page-title{font-size:31px}.footer-inner{align-items:flex-start;flex-direction:column}}
    </style>
    <style>
        .brand{display:block;width:190px;flex:none}.brand img{display:block;width:100%;height:auto;max-height:48px}.footer .brand{width:156px}.footer .brand img{max-height:40px}
        .navlinks a{display:inline-flex;align-items:center;gap:5px}.navlink-icon{width:15px;height:15px;flex:none;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
        .admin-payment-shortcut{display:none}
        .role-chat-shortcut{display:none}
        .logout-button{border-color:#b42318;color:#b42318;background:#fff7f5}
        .logout-button:hover{background:#b42318;color:#fff}
        @media(max-width:1440px) and (min-width:901px){.nav{gap:15px}.nav>.brand-lockup{width:170px}.navlinks{gap:14px}.navlinks a[href*="#kategori"],.navlinks a[href*="#cerita"]{display:none}.navright{gap:8px}.search{width:170px}.navright .signin{padding:8px 9px;font-size:11px}}
        .pagination nav{display:flex;align-items:center;justify-content:space-between;gap:12px}
        .pagination nav>div:first-child{display:none}
        .pagination nav>div:last-child{display:flex;flex:1;align-items:center;justify-content:space-between;gap:12px}
        .pagination nav>div:last-child>div:last-child{display:flex;align-items:center}
        .pagination nav svg{display:block;flex:none;width:16px!important;height:16px!important}
        .pagination nav a,.pagination nav span[aria-disabled="true"]>span,.pagination nav span[aria-current="page"]>span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:36px;padding:0 9px;border:1px solid var(--line);background:var(--white);color:var(--ink);font-size:11px}
        .pagination nav span[aria-current="page"]>span{background:var(--green);color:#fff}
        .pagination nav p{margin:0;color:var(--muted);font-size:10px}
        @media(max-width:900px){.admin-payment-shortcut,.role-chat-shortcut{display:inline-flex}}
        @media(max-width:900px){.nav{gap:12px}.brand{width:155px}.navlinks{display:none}.navright{gap:8px}.search input{width:150px}.signin{padding:8px 10px;font-size:10px}}
        @media(max-width:620px){.shell{width:calc(100% - 32px)}.nav{height:62px}.brand{width:135px}.navright{gap:6px}.search{width:36px;height:36px;padding:0;justify-content:center;border:0;background:transparent}.search input{display:none}.search:focus-within{position:absolute;left:16px;right:16px;top:42px;width:auto;background:var(--white);border:1px solid var(--line);padding:0 10px;z-index:3}.search:focus-within input{display:block;width:100%}.signin{padding:8px;font-size:9px}}
    </style>
    @stack('styles')
</head>
<body>
    <header class="shell nav">
        @auth
            @include('components.role-sidebar')
        @endauth
        <x-brand-lockup :href="route('home')" />
        <nav class="navlinks" aria-label="Navigasi utama">
            @if(auth()->guest() || auth()->user()->isCustomer())
                <a href="{{ route('home') }}"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20h14V9.5"/></svg>Home</a>
                <a href="{{ route('about') }}"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>Tentang Kami</a>
            @endif
            @guest
                <a href="{{ route('storefront.catalog') }}"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>Jelajahi</a><a href="{{ route('articles.index') }}"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M7.5 4.5h8.5L19 7v12.5a1.5 1.5 0 0 1-1.5 1.5h-10A1.5 1.5 0 0 1 6 19.5V6A1.5 1.5 0 0 1 7.5 4.5Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>Artikel</a><a href="{{ route('home') }}#kategori"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Kategori</a><a href="{{ route('home') }}#cerita"><svg class="navlink-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z"/></svg>Cerita Karya</a>
            @endguest
        </nav>
        <div class="navright">
            @if(auth()->guest() || auth()->user()->isCustomer())
                <form class="search" action="{{ route('storefront.catalog') }}" method="GET" aria-label="Cari produk">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari karya, perajin, daerah..." aria-label="Cari karya, perajin, daerah">
                </form>
            @endif
            @auth
                @if(auth()->user()->isAdmin())<a class="signin admin-payment-shortcut" href="{{ route('admin.payments.index') }}" style="display:inline-flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="M2.5 9h19M6 14h5"/></svg>Pembayaran</a>@endif
                @if(auth()->user()->isCustomer())<a class="signin role-chat-shortcut" href="{{ route('chat.customer.index') }}" style="display:inline-flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20.5 11.5a8.2 8.2 0 0 1-8.5 8 9 9 0 0 1-4-.9L3 20l1.4-4.1A7.7 7.7 0 0 1 3.5 11.5a8.2 8.2 0 0 1 8.5-8 8.2 8.2 0 0 1 8.5 8Z"/></svg>Chat</a>@elseif(auth()->user()->isArtisan())<a class="signin role-chat-shortcut" href="{{ route('artisan.chats.index') }}" style="display:inline-flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20.5 11.5a8.2 8.2 0 0 1-8.5 8 9 9 0 0 1-4-.9L3 20l1.4-4.1A7.7 7.7 0 0 1 3.5 11.5a8.2 8.2 0 0 1 8.5-8 8.2 8.2 0 0 1 8.5 8Z"/></svg>Chat</a>@endif
                <a class="signin profile-link" href="{{ route('profile.edit') }}"><x-profile-avatar :user="auth()->user()" />Profil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="signin logout-button" type="submit" style="display:inline-flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>Keluar</button></form>
            @else
                <a class="signin" href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:6px"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c.5-3.5 3.1-5.5 7-5.5s6.5 2 7 5.5"/></svg>Masuk</a>
            @endauth
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="footer"><div class="shell footer-inner"><x-brand-lockup :href="route('home')" /><span>Membeli Karya, Membawa Pulang Cerita.</span><span>© {{ date('Y') }} KaryaLokal.id</span></div></footer>
    @stack('scripts')
</body>
</html>
