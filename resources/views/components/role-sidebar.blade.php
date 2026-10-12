@php
    $role = auth()->user()->role;
    $roleLabel = match ($role) {
        'admin' => 'Admin',
        'artisan' => 'Perajin',
        'curator' => 'Kurator',
        'customer_service' => 'Layanan pelanggan',
        default => 'Pembeli',
    };
    $menuIcons = [
        'Dashboard' => 'dashboard',
        'Artikel' => 'article',
        'Kelola akun' => 'users',
        'Verifikasi pembayaran' => 'payment',
        'Laporan penjualan' => 'sales',
        'QR karya' => 'qr',
        'Rekomendasi SAW' => 'saw',
        'Karya saya' => 'products',
        'Tambah karya' => 'add',
        'Chat pembeli' => 'chat',
        'Antrean verifikasi' => 'verify',
        'Tiket bantuan' => 'help',
        'Beranda' => 'home',
        'Jelajahi karya' => 'search',
        'Pesanan saya' => 'orders',
        'Wishlist' => 'wishlist',
        'Chat perajin' => 'chat',
        'Bantuan' => 'help',
    ];
    $menuItems = match ($role) {
        'admin' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Artikel', 'route' => 'admin.articles.index', 'active' => 'admin.articles.*'],
            ['label' => 'Kelola akun', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
            ['label' => 'Verifikasi pembayaran', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*'],
            ['label' => 'Laporan penjualan', 'route' => 'admin.sales.index', 'active' => 'admin.sales.*'],
            ['label' => 'QR karya', 'route' => 'admin.qr.index', 'active' => 'admin.qr.*'],
            ['label' => 'Rekomendasi SAW', 'route' => 'admin.saw.index', 'active' => 'admin.saw.*'],
        ],
        'artisan' => [
            ['label' => 'Dashboard', 'route' => 'artisan.dashboard', 'active' => 'artisan.dashboard'],
            ['label' => 'Karya saya', 'route' => 'artisan.products.index', 'active' => ['artisan.products.index', 'artisan.products.show', 'artisan.products.edit']],
            ['label' => 'Tambah karya', 'route' => 'artisan.products.create', 'active' => 'artisan.products.create'],
            ['label' => 'Laporan penjualan', 'route' => 'artisan.sales.index', 'active' => 'artisan.sales.*'],
            ['label' => 'Chat pembeli', 'route' => 'artisan.chats.index', 'active' => 'artisan.chats.*'],
        ],
        'curator' => [
            ['label' => 'Dashboard', 'route' => 'curator.dashboard', 'active' => 'curator.dashboard'],
            ['label' => 'Antrean verifikasi', 'route' => 'curator.products.index', 'active' => 'curator.products.*'],
        ],
        'customer_service' => [
            ['label' => 'Dashboard', 'route' => 'support.dashboard', 'active' => 'support.dashboard'],
            ['label' => 'Tiket bantuan', 'route' => 'support.tickets.index', 'active' => 'support.tickets.*'],
        ],
        default => [
            ['label' => 'Beranda', 'route' => 'home', 'active' => 'home'],
            ['label' => 'Jelajahi karya', 'route' => 'storefront.catalog', 'active' => 'storefront.catalog'],
            ['label' => 'Artikel', 'route' => 'articles.index', 'active' => 'articles.*'],
            ['label' => 'Pesanan saya', 'route' => 'orders.index', 'active' => 'orders.*'],
            ['label' => 'Wishlist', 'route' => 'wishlist.index', 'active' => 'wishlist.*'],
            ['label' => 'Chat perajin', 'route' => 'chat.customer.index', 'active' => 'chat.customer.*'],
            ['label' => 'Bantuan', 'route' => 'support.customer.index', 'active' => 'support.customer.*'],
        ],
    };
@endphp

<button class="role-menu-trigger" type="button" aria-controls="role-sidebar-panel" aria-expanded="false" data-sidebar-open>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    <span>Menu</span>
</button>
<div class="role-sidebar-scrim" data-sidebar-close hidden></div>
<aside class="role-sidebar-panel" id="role-sidebar-panel" aria-label="Menu {{ $roleLabel }}" hidden>
    <div class="role-sidebar-heading">
        <div><span class="eyebrow">Ruang kerja</span><h2>{{ $roleLabel }}</h2></div>
        <button type="button" class="role-sidebar-close" aria-label="Tutup menu" data-sidebar-close>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <nav class="role-sidebar-links" aria-label="Menu {{ $roleLabel }}">
        @foreach($menuItems as $item)
            @php($isMenuActive = is_array($item['active']) ? request()->routeIs($item['active']) : request()->routeIs($item['active']))
            <a href="{{ route($item['route']) }}" @class(['role-sidebar-link', 'is-active' => $isMenuActive]) @if($isMenuActive) aria-current="page" @endif>
                <svg class="role-sidebar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @switch($menuIcons[$item['label']] ?? 'dashboard')
                        @case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
                        @case('article')<path d="M5 4.5A2.5 2.5 0 0 1 7.5 2H20v18H7.5A2.5 2.5 0 0 0 5 22z"/><path d="M5 4.5v17.5M9 7h7M9 11h7"/>@break
                        @case('users')<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.4-3.4 2.7-5.3 6.5-5.3 2 0 3.5.5 4.6 1.5M16 4.8a3.4 3.4 0 0 1 0 6.5M16 14.7c3.1.3 4.9 2 5.3 5.3"/>@break
                        @case('payment')<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="M2.5 9h19M6 14h5"/>@break
                        @case('sales')<path d="M3 20.5h18M5 17V9h4v8M10 17V4h4v13M15 17v-5h4v5"/>@break
                        @case('qr')<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v2M17 20h4M20 18v3"/>@break
                        @case('saw')<path d="M4 19V5M4 19h17M8 16v-5h3v5M14 16V7h3v9M20 16v-3h2"/>@break
                        @case('products')<path d="m12 3 9 4.5v9L12 21l-9-4.5v-9z"/><path d="m3.5 7.7 8.5 4.5 8.5-4.5M12 12.2V21M8 5l9 4.7"/>@break
                        @case('add')<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/>@break
                        @case('chat')<path d="M20.5 11.5a8.2 8.2 0 0 1-8.5 8 9 9 0 0 1-4-.9L3 20l1.4-4.1A7.7 7.7 0 0 1 3.5 11.5a8.2 8.2 0 0 1 8.5-8 8.2 8.2 0 0 1 8.5 8Z"/><path d="M8 11.5h.01M12 11.5h.01M16 11.5h.01"/>@break
                        @case('verify')<path d="M12 3 20 6v5c0 5-3.5 8.1-8 10-4.5-1.9-8-5-8-10V6z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>@break
                        @case('help')<circle cx="12" cy="12" r="9"/><path d="M9.7 9a2.4 2.4 0 1 1 4.3 1.5c-1.1 1.3-2 1.4-2 3M12 17h.01"/>@break
                        @case('home')<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z"/>@break
                        @case('search')<circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/>@break
                        @case('orders')<path d="M6 3h12v18l-6-3-6 3z"/><path d="M9 8h6M9 12h6"/>@break
                        @case('wishlist')<path d="M20.8 8.8c0 5.2-8.8 10.3-8.8 10.3S3.2 14 3.2 8.8A4.3 4.3 0 0 1 12 6.5a4.3 4.3 0 0 1 8.8 2.3Z"/>@break
                    @endswitch
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <a class="role-sidebar-profile" href="{{ route('profile.edit') }}">
        <x-profile-avatar :user="auth()->user()" :size="34" />
        <span><strong>{{ auth()->user()->name }}</strong><small>Profil akun</small></span>
    </a>
</aside>

@once
    <style>
        .role-menu-trigger{display:inline-flex;align-items:center;gap:7px;min-height:38px;padding:0 11px;border:1px solid var(--line);background:var(--white);color:var(--ink);font-size:11px;font-weight:700;cursor:pointer}
        .role-menu-trigger svg{width:17px;height:17px}
        .role-sidebar-scrim{position:fixed;inset:0;z-index:40;background:rgba(16,31,26,.42)}
        .role-sidebar-scrim[hidden],.role-sidebar-panel[hidden]{display:none}
        .role-sidebar-panel{position:fixed;z-index:41;inset:0 auto 0 0;width:min(300px,calc(100vw - 48px));height:100vh;height:100dvh;padding:22px 18px;background:var(--paper);border-right:1px solid var(--line);box-shadow:12px 0 40px rgba(16,31,26,.14);overflow-y:auto}
        .role-sidebar-heading{display:flex;justify-content:space-between;align-items:start;gap:12px;padding-bottom:19px;border-bottom:1px solid var(--line)}
        .role-sidebar-heading h2{margin:6px 0 0;font:600 23px/1.2 var(--serif)}
        .role-sidebar-close{display:grid;place-items:center;width:36px;height:36px;border:1px solid var(--line);background:var(--white);color:var(--ink);cursor:pointer}
        .role-sidebar-close svg{width:17px;height:17px}
        .role-sidebar-links{display:grid;gap:4px;padding-top:14px}
        .role-sidebar-link{display:flex;align-items:center;gap:11px;padding:12px 11px;border-left:3px solid transparent;color:var(--muted);font-size:12px;font-weight:600}
        .role-sidebar-icon{width:18px;height:18px;flex:none}
        .role-sidebar-link:hover,.role-sidebar-link.is-active{border-left-color:var(--orange);background:#eeede5;color:var(--ink)}
        .role-sidebar-profile{display:flex;align-items:center;gap:10px;margin-top:18px;padding:13px 8px;border-top:1px solid var(--line);font-size:11px}
        .role-sidebar-profile span:last-child{display:grid;gap:3px;min-width:0}
        .role-sidebar-profile strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .role-sidebar-profile small{color:var(--muted);font-size:9px}
        @media(max-width:620px){.role-menu-trigger{width:37px;height:37px;min-height:37px;justify-content:center;padding:0}.role-menu-trigger span{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}}
        @media(prefers-reduced-motion:no-preference){.role-sidebar-panel:not([hidden]){animation:role-sidebar-in .18s ease-out}@keyframes role-sidebar-in{from{transform:translateX(-12px);opacity:.7}to{transform:translateX(0);opacity:1}}}
    </style>
    <script>
        (() => {
            const trigger = document.querySelector('[data-sidebar-open]');
            const panel = document.querySelector('#role-sidebar-panel');
            const scrim = document.querySelector('.role-sidebar-scrim');
            const closeButtons = document.querySelectorAll('[data-sidebar-close]');
            if (!trigger || !panel || !scrim) return;

            const close = () => {
                panel.hidden = true;
                scrim.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
                trigger.focus();
            };

            trigger.addEventListener('click', () => {
                panel.hidden = false;
                scrim.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
                panel.querySelector('button[data-sidebar-close]')?.focus();
            });
            closeButtons.forEach((button) => button.addEventListener('click', close));
            panel.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !panel.hidden) close();
            });
        })();
    </script>
@endonce