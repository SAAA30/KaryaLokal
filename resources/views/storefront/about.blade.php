@extends('layouts.storefront')

@section('title', 'Tentang Kami — KaryaLokal.id')

@section('content')
<section class="shell about-page">
    <div class="about-hero">
        <div class="about-copy">
            <div class="eyebrow">Tentang Kami</div>
            <h1 class="page-title">Tentang KaryaLokal</h1>
            <p class="lead">Membawa karya Nusantara ke rumah Anda dengan cerita, kualitas, dan sentuhan tangan perajin yang autentik.</p>
            <div class="about-actions">
                <a class="primary" href="{{ route('storefront.catalog') }}">Jelajahi karya</a>
                <a class="signin" href="mailto:hello@karyalokal.id">Hubungi kami</a>
            </div>
        </div>
        <div class="about-visual">
            <div class="about-badge">Karya asli • Dipilih dengan hati</div>
            <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=900&q=85" alt="Tim KaryaLokal dan produk lokal">
        </div>
    </div>

    <div class="about-stats">
        <article>
            <strong>500+</strong>
            <span>Karya dipajang</span>
        </article>
        <article>
            <strong>80+</strong>
            <span>Perajin aktif</span>
        </article>
        <article>
            <strong>20+</strong>
            <span>Kota penghasil karya</span>
        </article>
        <article>
            <strong>100%</strong>
            <span>Asli &amp; terverifikasi</span>
        </article>
    </div>

    <div class="story-grid">
        <section class="story-panel">
            <div class="eyebrow">Cerita kami</div>
            <h2>Karya lokal yang punya identitas, proses, dan jiwa.</h2>
            <p>
                KaryaLokal lahir dari keinginan untuk menghubungkan para perajin Indonesia dengan pembeli yang menghargai kualitas, keberlanjutan, dan cerita di balik setiap produk.
                Kami percaya setiap karya bukan sekadar benda, tetapi jejak tangan, budaya, dan semangat yang membangunnya.
            </p>
            <p>
                Dengan pendekatan yang transparan dan manusiawi, kami membantu produk lokal sampai ke rumah pembeli sambil menjaga nilai seni, tradisi, dan ekonomi kreatif di setiap daerah.
            </p>
        </section>

        <aside class="value-panel">
            <div class="eyebrow">Nilai yang kami jaga</div>
            <ul>
                <li>Produk asli hasil tangan perajin lokal</li>
                <li>Storytelling yang jelas untuk setiap karya</li>
                <li>Verifikasi kualitas sebelum dipasarkan</li>
                <li>Pengalaman belanja yang aman dan menyenangkan</li>
            </ul>
        </aside>
    </div>

    <div class="mission-grid">
        <article>
            <div class="eyebrow">Visi</div>
            <p>Menjadi marketplace utama karya lokal yang mendorong keberlanjutan ekonomi kreatif di Indonesia.</p>
        </article>
        <article>
            <div class="eyebrow">Misi</div>
            <p>Membuka akses pasar bagi perajin, menjaga keaslian produk, dan memperkenalkan cerita di balik setiap karya.</p>
        </article>
        <article>
            <div class="eyebrow">Komitmen</div>
            <p>Transparansi, kualitas, kolaborasi, dan rasa hormat terhadap tradisi serta kreativitas lokal.</p>
        </article>
    </div>

    <div class="contact-grid">
        <section class="contact-panel">
            <div class="eyebrow">Alamat &amp; lokasi</div>
            <h2>KaryaLokal Studio</h2>
            <p>
                Jl. Raya Kledokan No. 17<br>
                Bantul, Yogyakarta 55184<br>
                Indonesia
            </p>
            <p>
                WhatsApp: +62 812-3456-7890<br>
                Email: hello@karyalokal.id
            </p>
            <p class="muted">Jam operasional: Senin–Sabtu, 09.00–17.00 WIB</p>
        </section>

        <section class="map-panel">
            <div class="eyebrow">Peta lokasi</div>
            <div class="map-wrap">
                <iframe
                    src="https://www.google.com/maps?q=Bantul%20Yogyakarta&output=embed"
                    width="100%"
                    height="340"
                    style="border:0;display:block"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Lokasi KaryaLokal di Bantul, Yogyakarta"
                ></iframe>
            </div>
        </section>
    </div>
</section>
@endsection

@push('styles')
<style>
    .about-page { padding: 42px 0 0; }
    .about-hero { display: grid; grid-template-columns: 1.05fr .95fr; gap: 28px; align-items: center; }
    .about-copy { padding-right: 10px; }
    .lead { max-width: 640px; margin: 0; font-size: 15px; line-height: 1.9; color: var(--muted); }
    .about-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 22px; }
    .about-visual { position: relative; min-height: 420px; }
    .about-visual img { display: block; width: 100%; height: 100%; min-height: 420px; object-fit: cover; border: 1px solid var(--line); }
    .about-badge { position: absolute; left: 18px; top: 18px; z-index: 1; background: rgba(255,255,255,0.9); border: 1px solid var(--line); color: var(--green); font-size: 10px; font-weight: 700; letter-spacing: 0.7px; text-transform: uppercase; padding: 9px 12px; }
    .about-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin: 34px 0; }
    .about-stats article { background: var(--white); border: 1px solid var(--line); padding: 22px 18px; text-align: center; }
    .about-stats strong { display: block; font: 600 30px/1.1 var(--serif); color: var(--green); }
    .about-stats span { display: block; margin-top: 10px; font-size: 11px; color: var(--muted); }
    .story-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 20px; margin-top: 12px; }
    .story-panel, .value-panel, .contact-panel, .map-panel, .mission-grid article { background: var(--white); border: 1px solid var(--line); }
    .story-panel { padding: 26px 24px; }
    .story-panel h2 { margin: 12px 0 14px; font: 600 30px/1.2 var(--serif); }
    .story-panel p { margin: 0 0 14px; font-size: 13px; line-height: 1.9; color: var(--ink); }
    .value-panel { padding: 24px; }
    .value-panel ul { margin: 14px 0 0; padding-left: 18px; font-size: 13px; line-height: 1.9; }
    .mission-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; margin-top: 22px; }
    .mission-grid article { padding: 22px 20px; }
    .mission-grid p { margin: 12px 0 0; font-size: 13px; line-height: 1.8; }
    .contact-grid { display: grid; grid-template-columns: .92fr 1.08fr; gap: 20px; margin-top: 22px; padding-bottom: 52px; }
    .contact-panel, .map-panel { padding: 24px; }
    .contact-panel h2 { margin: 12px 0 10px; font: 600 28px/1.2 var(--serif); }
    .contact-panel p { margin: 0 0 10px; font-size: 13px; line-height: 1.8; }
    .map-wrap { margin-top: 14px; border: 1px solid var(--line); overflow: hidden; }
    @media (max-width: 900px) {
        .about-hero, .story-grid, .contact-grid, .mission-grid { grid-template-columns: 1fr; }
        .about-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 620px) {
        .about-page { padding-top: 30px; }
        .about-visual, .about-visual img { min-height: 280px; }
        .about-stats { grid-template-columns: 1fr 1fr; }
        .story-panel h2, .contact-panel h2 { font-size: 26px; }
        .page-title { font-size: 34px; }
    }
</style>
@endpush
