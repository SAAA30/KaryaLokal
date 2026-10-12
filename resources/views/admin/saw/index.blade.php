@extends('layouts.storefront')

@section('title', 'SPK Rekomendasi Produk — KaryaLokal.id')

@section('content')
<section class="shell saw-page">
    <a href="{{ route('admin.dashboard') }}" class="muted saw-back">← Dashboard admin</a>
    <div class="saw-heading">
        <div>
            <div class="eyebrow">Admin · Sistem pendukung keputusan</div>
            <h1 class="page-title">Rekomendasi produk</h1>
            <p class="muted">Peringkat produk approved berdasarkan harga, penjualan, ulasan, dan ketersediaan.</p>
        </div>
        <span class="saw-total">{{ number_format($ranking->count()) }} produk dinilai</span>
    </div>

    <section class="saw-method" aria-label="Metode Simple Additive Weighting">
        <div class="saw-method-title"><span aria-hidden="true">Σ</span><strong>Metode Simple Additive Weighting (SAW)</strong></div>
        <div class="saw-criteria">
            <span><b>C1</b> Harga <em>Cost · bobot 30%</em></span>
            <span><b>C2</b> Unit terjual <em>Benefit · bobot 30%</em></span>
            <span><b>C3</b> Rating pembeli <em>Benefit · bobot 20%</em></span>
            <span><b>C4</b> Stok tersedia <em>Benefit · bobot 20%</em></span>
        </div>
        <div class="saw-priority-legend">
            <span><b class="priority-pill high">Prioritas Tinggi</b> Skor ≥ 0,80</span>
            <span><b class="priority-pill normal">Normal</b> Skor 0,60–0,79</span>
            <span><b class="priority-pill evaluate">Evaluasi</b> Skor &lt; 0,60</span>
        </div>
        <p class="saw-formula">Cost dinormalisasi dengan harga minimum ÷ harga produk; benefit dengan nilai produk ÷ nilai maksimum. Skor akhir adalah jumlah nilai normalisasi × bobot.</p>
    </section>

    <div class="saw-summary">
        <article class="saw-summary-card high-card"><span>Prioritas tinggi</span><strong>{{ number_format($priorities['Prioritas Tinggi']) }}</strong><small>produk · skor ≥ 0,80</small></article>
        <article class="saw-summary-card normal-card"><span>Normal</span><strong>{{ number_format($priorities['Normal']) }}</strong><small>produk · skor 0,60–0,79</small></article>
        <article class="saw-summary-card eval-card"><span>Evaluasi</span><strong>{{ number_format($priorities['Evaluasi']) }}</strong><small>produk · skor &lt; 0,60</small></article>
    </div>

    <div class="saw-charts">
        <section class="saw-panel ranking-panel">
            <div class="saw-panel-heading"><h2>Top 10 produk berdasarkan skor SAW</h2><span class="muted">Skala 0–1</span></div>
            @forelse($topProducts as $row)
                <div class="rank-bar-row">
                    <span class="rank-number">{{ $row['rank'] }}</span>
                    <a href="{{ route('admin.products.edit', $row['product']) }}" class="rank-product">{{ $row['product']->name }}</a>
                    <div class="rank-track"><span class="rank-fill {{ $row['priority'] === 'Prioritas Tinggi' ? 'is-high' : ($row['priority'] === 'Normal' ? 'is-normal' : 'is-evaluate') }}" data-width="{{ number_format($row['score'] * 100, 2, '.', '') }}"></span></div>
                    <strong class="rank-score">{{ number_format($row['score'], 4) }}</strong>
                </div>
            @empty
                <p class="muted saw-empty-chart">Belum ada produk approved untuk dihitung.</p>
            @endforelse
        </section>

        <section class="saw-panel distribution-panel">
            <div class="saw-panel-heading"><h2>Distribusi prioritas</h2></div>
            <div class="priority-chart-wrap">
                <div class="priority-donut" role="img" aria-label="Distribusi {{ $priorities['Prioritas Tinggi'] }} prioritas tinggi, {{ $priorities['Normal'] }} normal, {{ $priorities['Evaluasi'] }} evaluasi" data-high-percent="{{ number_format($highPercent, 2, '.', '') }}" data-normal-percent="{{ number_format($normalPercent, 2, '.', '') }}">
                    <div><strong>{{ number_format($ranking->count()) }}</strong><span>produk</span></div>
                </div>
                <div class="priority-legend-list">
                    <div><i class="legend-dot high-dot"></i><span>Prioritas tinggi</span><strong>{{ number_format($priorities['Prioritas Tinggi']) }}</strong></div>
                    <div><i class="legend-dot normal-dot"></i><span>Normal</span><strong>{{ number_format($priorities['Normal']) }}</strong></div>
                    <div><i class="legend-dot eval-dot"></i><span>Evaluasi</span><strong>{{ number_format($priorities['Evaluasi']) }}</strong></div>
                </div>
            </div>
        </section>

        <section class="saw-panel scatter-panel">
            <div class="saw-panel-heading"><h2>Scatter: harga vs unit terjual</h2><span class="muted">Order lunas</span></div>
            <div class="scatter-chart" role="img" aria-label="Grafik sebar harga produk dan jumlah unit terjual">
                <div class="scatter-y-label">Unit terjual</div>
                <div class="scatter-plot">
                    <div class="scatter-gridline grid-top"></div><div class="scatter-gridline grid-middle"></div><div class="scatter-gridline grid-bottom"></div>
                    @foreach($scatterPoints as $point)
                        <span class="scatter-point {{ $point['priority'] === 'Prioritas Tinggi' ? 'is-high' : ($point['priority'] === 'Normal' ? 'is-normal' : 'is-evaluate') }}" title="{{ $point['name'] }} · Rp{{ number_format($point['price'], 0, ',', '.') }} · {{ $point['sold_units'] }} unit" data-left="{{ number_format($point['left'], 2, '.', '') }}" data-bottom="{{ number_format($point['bottom'], 2, '.', '') }}"></span>
                    @endforeach
                    <span class="scatter-axis-y">{{ $maximumSales }}</span><span class="scatter-axis-x">Rp{{ number_format($maximumPrice, 0, ',', '.') }}</span>
                </div>
                <div class="scatter-x-label">Harga produk</div>
            </div>
            <div class="scatter-legend"><span><i class="legend-dot high-dot"></i>Prioritas tinggi</span><span><i class="legend-dot normal-dot"></i>Normal</span><span><i class="legend-dot eval-dot"></i>Evaluasi</span></div>
        </section>
    </div>

    <section class="saw-results" id="hasil-ranking">
        <div class="saw-results-heading">
            <div><h2>Hasil ranking SAW</h2><p class="muted">Klik nama produk untuk mengubah data yang menjadi kriteria penilaian.</p></div>
            <form method="GET" action="{{ route('admin.saw.index') }}" class="saw-search">
                <label class="sr-only" for="saw-search">Cari produk</label>
                <input id="saw-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama produk atau perajin...">
                <button type="submit" aria-label="Cari produk">Cari</button>
            </form>
        </div>
        <div class="saw-table-wrap">
            <table class="saw-table">
                <thead><tr><th>Rank</th><th>Nama produk / perajin</th><th>Harga<br><small>C1 · cost</small></th><th>Terjual<br><small>C2 · benefit</small></th><th>Rating<br><small>C3 · benefit</small></th><th>Stok<br><small>C4 · benefit</small></th><th>Skor SAW</th><th>Prioritas</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($visibleRanking as $row)
                        @php($product = $row['product'])
                        <tr>
                            <td><span class="rank-cell">{{ $row['rank'] }}</span></td>
                            <td><a class="saw-product-name" href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => route('admin.saw.index')]) }}" aria-label="Edit data produk {{ $product->name }}">{{ $product->name }}</a><small>{{ $product->artisan?->name ?? 'Perajin' }} · {{ $product->category?->name ?? 'Tanpa kategori' }}</small></td>
                            <td><strong>Rp{{ number_format($row['price'], 0, ',', '.') }}</strong><small>Norm. {{ number_format($row['normalized']['price'], 3) }}</small></td>
                            <td><strong>{{ number_format($row['sold_units']) }}</strong><small>Norm. {{ number_format($row['normalized']['sold_units'], 3) }}</small></td>
                            <td><strong>{{ number_format($row['rating'], 1) }} / 5</strong><small>Norm. {{ number_format($row['normalized']['rating'], 3) }}</small></td>
                            <td><strong>{{ number_format($row['stock']) }}</strong><small>Norm. {{ number_format($row['normalized']['stock'], 3) }}</small></td>
                            <td><span class="score-cell">{{ number_format($row['score'], 4) }}</span></td>
                            <td><span class="priority-pill {{ $row['priority'] === 'Prioritas Tinggi' ? 'high' : ($row['priority'] === 'Normal' ? 'normal' : 'evaluate') }}">{{ $row['priority'] }}</span></td>
                            <td><a class="saw-edit" href="{{ route('admin.products.edit', ['product' => $product, 'return_to' => route('admin.saw.index')]) }}" aria-label="Edit data produk {{ $product->name }}">Ubah data</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="saw-no-results">Tidak ada produk yang cocok dengan pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection

@push('styles')
<style>
    .saw-page{padding:30px 0 54px;color:var(--ink)}
    .saw-back{font-size:11px}
    .saw-heading{display:flex;align-items:end;justify-content:space-between;gap:18px;margin:18px 0}
    .saw-heading .page-title{margin:7px 0 5px}
    .saw-heading p{margin:0;font-size:12px}
    .saw-total{padding:8px 11px;background:#e6efdf;color:var(--green);font-size:11px;font-weight:700;white-space:nowrap}
    .saw-method{padding:14px 17px;border-left:4px solid var(--green);background:#edf4e9}
    .saw-method-title{display:flex;align-items:center;gap:9px;font-size:12px}
    .saw-method-title>span{display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:var(--green);color:white;font-weight:700}
    .saw-criteria,.saw-priority-legend{display:flex;gap:8px 18px;flex-wrap:wrap;margin-top:10px;font-size:10px}
    .saw-criteria span{white-space:nowrap}.saw-criteria b{color:var(--green)}.saw-criteria em{color:var(--muted);font-style:normal}
    .saw-priority-legend{gap:8px 16px;align-items:center}.saw-priority-legend>span{display:flex;align-items:center;gap:6px}
    .saw-formula{margin:10px 0 0;color:var(--muted);font-size:10px;line-height:1.6}
    .saw-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin:16px 0}
    .saw-summary-card{display:grid;gap:5px;min-height:105px;padding:15px 17px;background:var(--white);border:1px solid var(--line);border-left:4px solid var(--green)}
    .saw-summary-card span{font-size:10px;font-weight:700;text-transform:uppercase;color:var(--muted)}
    .saw-summary-card strong{font:700 29px/1 var(--serif)}.saw-summary-card small{color:var(--muted);font-size:10px}
    .saw-summary-card.normal-card{border-left-color:#e7b323}.saw-summary-card.normal-card strong{color:#c38e08}.saw-summary-card.eval-card{border-left-color:#d9424c}.saw-summary-card.eval-card strong{color:#c4313d}
    .saw-charts{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(270px,.9fr);gap:14px;margin:15px 0}
    .saw-panel,.saw-results{min-width:0;background:var(--white);border:1px solid var(--line)}
    .saw-panel{padding:16px}.saw-panel-heading{display:flex;justify-content:space-between;align-items:baseline;gap:10px;margin-bottom:14px}
    .saw-panel-heading h2,.saw-results-heading h2{margin:0;font:600 17px var(--serif)}.saw-panel-heading>.muted{font-size:10px}
    .rank-bar-row{display:grid;grid-template-columns:22px minmax(110px,1fr) minmax(80px,1.5fr) 44px;gap:9px;align-items:center;margin:10px 0;font-size:10px}
    .rank-number{color:var(--muted);font-weight:700;text-align:right}.rank-product{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--ink)}.rank-product:hover,.saw-product-name:hover{color:var(--green);text-decoration:underline;text-underline-offset:3px}
    .rank-track{height:9px;background:#f0efe8;overflow:hidden}.rank-fill{display:block;height:100%;min-width:2px}.rank-fill.is-high{background:#28945a}.rank-fill.is-normal{background:#f0b323}.rank-fill.is-evaluate{background:#d9424c}.rank-score{text-align:right;font-size:10px}
    .saw-empty-chart{font-size:11px}
    .priority-chart-wrap{display:flex;align-items:center;justify-content:center;gap:20px;min-height:190px}
    .priority-donut{display:grid;place-items:center;width:158px;aspect-ratio:1;border-radius:50%;flex:none}
    .priority-donut>div{display:grid;place-content:center;width:92px;aspect-ratio:1;border-radius:50%;background:var(--white);text-align:center}
    .priority-donut strong{font:700 25px/1 var(--serif)}.priority-donut span{margin-top:4px;color:var(--muted);font-size:9px}
    .priority-legend-list{display:grid;gap:12px;font-size:10px}.priority-legend-list>div{display:grid;grid-template-columns:10px minmax(100px,1fr) auto;gap:7px;align-items:center}.priority-legend-list strong{font-size:11px}
    .legend-dot{display:inline-block;width:9px;height:9px;border-radius:50%}.high-dot{background:#28945a}.normal-dot{background:#f0b323}.eval-dot{background:#d9424c}
    .scatter-panel{grid-column:1/-1}
    .scatter-chart{position:relative;padding:12px 20px 28px 48px}
    .scatter-y-label{position:absolute;left:0;top:50%;transform:translateY(-50%) rotate(-90deg);transform-origin:center;font-size:9px;color:var(--muted)}
    .scatter-plot{position:relative;height:170px;border-left:1px solid #aab2ad;border-bottom:1px solid #aab2ad;background:repeating-linear-gradient(to bottom,transparent 0,transparent 41px,#e7e9e4 42px),repeating-linear-gradient(to right,transparent 0,transparent 24.8%,#e7e9e4 25%)}
    .scatter-point{position:absolute;width:9px;height:9px;border:1px solid white;border-radius:50%;transform:translate(-50%,50%);opacity:.82}.scatter-point.is-high{background:#20804d}.scatter-point.is-normal{background:#e7ae18}.scatter-point.is-evaluate{background:#d9424c}
    .scatter-axis-y,.scatter-axis-x{position:absolute;color:var(--muted);font-size:9px}.scatter-axis-y{left:-23px;top:-4px}.scatter-axis-x{right:0;bottom:-17px}.scatter-x-label{text-align:center;color:var(--muted);font-size:9px;margin-top:10px}.scatter-legend{display:flex;justify-content:center;gap:15px;flex-wrap:wrap;color:var(--muted);font-size:9px}.scatter-legend span{display:flex;align-items:center;gap:5px}
    .saw-results{margin-top:15px}
    .saw-results-heading{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:15px 16px;border-bottom:1px solid var(--line)}
    .saw-results-heading p{margin:4px 0 0;font-size:10px}
    .saw-search{display:flex;gap:7px;width:min(390px,48%)}.saw-search input{min-width:0;flex:1;height:37px;padding:0 10px;border:1px solid var(--line);background:var(--white);color:var(--ink);font:11px var(--sans)}.saw-search button{height:37px;padding:0 14px;border:0;background:var(--green);color:#fff;font-size:10px;font-weight:700;cursor:pointer}
    .saw-table-wrap{overflow-x:auto}.saw-table{width:100%;border-collapse:collapse;text-align:left;font-size:10px}.saw-table th{padding:10px 9px;background:#f5f4ee;color:var(--muted);font-size:9px;text-transform:uppercase;white-space:nowrap}.saw-table th small{font-size:8px;text-transform:none}.saw-table td{padding:10px 9px;border-top:1px solid var(--line);vertical-align:middle}.saw-table td:not(:nth-child(2)){white-space:nowrap}.saw-table td:nth-child(n+3):nth-child(-n+6){text-align:right}
    .saw-table td:nth-child(2){min-width:190px}.saw-product-name{display:block;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:700}.saw-table td small{display:block;margin-top:3px;color:var(--muted);font-size:8px}.rank-cell{display:grid;place-items:center;width:23px;height:23px;background:#f0efe8;font-weight:700}.score-cell{font-weight:700;color:var(--green)}
    .priority-pill{display:inline-flex;padding:5px 7px;font-size:8px;font-weight:700;white-space:nowrap}.priority-pill.high{background:#e1f0e5;color:#21663c}.priority-pill.normal{background:#fff2c9;color:#825d00}.priority-pill.evaluate{background:#fce3e4;color:#a32832}
    .saw-edit{display:inline-flex;align-items:center;justify-content:center;min-width:44px;height:30px;padding:0 8px;border:1px solid var(--green);color:var(--green);font-weight:700}.saw-edit:hover{background:var(--green);color:white}.saw-no-results{padding:28px!important;text-align:center;color:var(--muted)}
    @media(max-width:900px){.saw-charts{grid-template-columns:1fr}.scatter-panel{grid-column:auto}.priority-chart-wrap{min-height:160px}}
    @media(max-width:620px){.saw-page{padding-top:22px}.saw-heading{align-items:start;flex-direction:column}.saw-summary{gap:7px}.saw-summary-card{min-height:95px;padding:11px 10px}.saw-summary-card strong{font-size:24px}.saw-summary-card span{font-size:8px}.saw-criteria{display:grid;grid-template-columns:1fr 1fr}.saw-results-heading{align-items:stretch;flex-direction:column}.saw-search{width:100%}.rank-bar-row{grid-template-columns:18px minmax(82px,1fr) minmax(56px,1fr) 39px;gap:6px;font-size:9px}.priority-chart-wrap{gap:15px}.priority-donut{width:138px}.saw-table{min-width:900px}}
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.rank-fill[data-width]').forEach((bar) => {
        const width = Number(bar.dataset.width);
        if (Number.isFinite(width)) bar.style.width = `${Math.max(0, Math.min(100, width))}%`;
    });

    const priorityDonut = document.querySelector('.priority-donut[data-high-percent]');
    if (priorityDonut) {
        const highPercent = Number(priorityDonut.dataset.highPercent) || 0;
        const normalPercent = Number(priorityDonut.dataset.normalPercent) || highPercent;
        priorityDonut.style.background = `conic-gradient(#28945a 0 ${highPercent}%, #f0b323 ${highPercent}% ${normalPercent}%, #d9424c ${normalPercent}% 100%)`;
    }

    document.querySelectorAll('.scatter-point[data-left][data-bottom]').forEach((point) => {
        const left = Number(point.dataset.left);
        const bottom = Number(point.dataset.bottom);
        if (Number.isFinite(left)) point.style.left = `${Math.max(0, Math.min(100, left))}%`;
        if (Number.isFinite(bottom)) point.style.bottom = `${Math.max(0, Math.min(100, bottom))}%`;
    });
</script>
@endpush