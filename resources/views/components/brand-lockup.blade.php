@props(['href', 'class' => ''])

<a class="brand-lockup {{ $class }}" href="{{ $href }}" aria-label="KaryaLokal.id">
    <img class="brand-lockup-mark" src="{{ asset('images/karyalokal-mark.png') }}" alt="">
    <span class="brand-lockup-name">Karya<span>Lokal</span><small>.id</small></span>
</a>

@once
    <style>
        .brand-lockup{display:flex;align-items:center;gap:8px;width:190px;flex:none;color:var(--ink);text-decoration:none}
        .brand-lockup-mark{display:block;width:42px;height:42px;flex:none;object-fit:contain}
        .brand-lockup-name{font:700 21px/1 var(--serif);letter-spacing:0;white-space:nowrap}
        .brand-lockup-name span{color:var(--orange)}
        .brand-lockup-name small{margin-left:1px;font:600 9px var(--sans);vertical-align:top}
        .footer .brand-lockup{width:185px}
        .auth-brand{margin-bottom:12px}
        @media(max-width:900px){.brand-lockup{width:175px}.brand-lockup-mark{width:38px;height:38px}.brand-lockup-name{font-size:19px}}
        @media(max-width:620px){.brand-lockup{width:155px;gap:6px}.brand-lockup-mark{width:34px;height:34px}.brand-lockup-name{font-size:17px}}
    </style>
@endonce