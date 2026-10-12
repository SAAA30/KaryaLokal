@extends('layouts.storefront')
@section('title', 'Karya Menunggu Verifikasi — Admin KaryaLokal')
@section('content')
<section class="shell admin-pending-products">
    <a class="muted" href="{{ route('admin.dashboard') }}">← Dashboard admin</a>
    <div class="pending-heading">
        <div><div class="eyebrow">Antrean produk</div><h1 class="page-title">Karya menunggu verifikasi</h1></div>
        <span class="pending-total">{{ number_format($products->total()) }} karya</span>
    </div>
    <div class="pending-table">
        <div class="pending-row pending-header"><span>Produk / Perajin</span><span>Kategori</span><span>Diajukan</span><span>Status</span></div>
        @forelse($products as $product)
            <a class="pending-row" href="{{ route('admin.pending-products.show', $product) }}">
                <span><strong>{{ $product->name }}</strong><small>{{ $product->artisan?->name ?? 'Perajin' }}</small></span>
                <span>{{ $product->category?->name ?? '-' }}</span>
                <span>{{ $product->created_at->format('d M Y') }}</span>
                <span class="verified">Menunggu</span>
            </a>
        @empty
            <p class="muted" style="padding:22px 0">Tidak ada karya yang menunggu verifikasi.</p>
        @endforelse
    </div>
    <div class="pagination">{{ $products->links() }}</div>
</section>
@endsection
@push('styles')
<style>
    .admin-pending-products{padding:36px 0 50px}
    .admin-pending-products>a{font-size:12px}
    .pending-heading{display:flex;align-items:end;justify-content:space-between;gap:14px;margin:17px 0}
    .pending-heading .page-title{margin-bottom:0}
    .pending-total{padding:8px 11px;background:#e6efdf;color:var(--green);font-size:13px;font-weight:700}
    .pending-table{padding:0 18px;background:var(--white);border:1px solid var(--line)}
    .pending-row{display:grid;grid-template-columns:minmax(200px,2fr) 1fr 1fr auto;align-items:center;gap:14px;min-height:58px;padding:10px 4px;border-bottom:1px solid var(--line);font-size:13px}
    .pending-row:last-child{border-bottom:0}
    a.pending-row:hover{color:var(--green);background:#f7f5ef}
    .pending-row>span:first-child{display:grid;gap:4px}
    .pending-row small{color:var(--muted);font-size:12px}
    .pending-header{min-height:42px;color:var(--muted);font-size:11px;font-weight:700;text-transform:uppercase}
    @media(max-width:700px){.pending-header{display:none}.pending-row{grid-template-columns:1fr auto;gap:7px}.pending-row>span:nth-child(2),.pending-row>span:nth-child(3){font-size:11px}.pending-row>span:last-child{grid-column:2;grid-row:1}}
</style>
@endpush
