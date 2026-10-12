@extends('layouts.storefront')

@section('title', 'Rekap Penjualan — Perajin KaryaLokal')

@section('content')
<section class="shell sales-report">
    <a class="muted" style="font-size:11px" href="{{ route('artisan.dashboard') }}">← Dashboard perajin</a>
    <div class="eyebrow" style="margin-top:20px">Pembayaran terverifikasi</div>
    <h1 class="page-title">Rekap penjualan</h1>
    <div class="report-metrics">
        <article><span>Nilai produk terjual</span><strong>Rp{{ number_format((float) $summary->total_sales, 0, ',', '.') }}</strong></article>
        <article><span>Order dibayar</span><strong>{{ number_format((int) $summary->paid_orders) }}</strong></article>
        <article><span>Unit terjual</span><strong>{{ number_format((int) $summary->units_sold) }}</strong></article>
    </div>
    <x-dashboard-chart title="Penjualan produk per bulan" :data="$monthlySalesSeries" type="line" color="#397cf6" />
    <section class="report-section">
        <h2 class="section-title">Produk dalam order dibayar</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead><tr><th>Order</th><th>Tanggal bayar</th><th>Customer</th><th>Produk</th><th>Jumlah</th><th>Nilai produk</th></tr></thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->order->order_number }}</td>
                            <td>{{ $item->order->paid_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $item->order->customer->name }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td>Rp{{ number_format((float) $item->unit_price * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Belum ada produk dalam order yang dibayar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $items->links() }}</div>
    </section>
</section>
@endsection

@push('styles')
<style>
    .sales-report{max-width:1100px;padding:36px 0 52px}
    .report-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:20px 0}
    .report-metrics article{display:grid;gap:8px;padding:16px;background:var(--white);border:1px solid var(--line)}
    .report-metrics span{color:var(--muted);font-size:10px}.report-metrics strong{font:600 22px var(--serif)}
    .report-section{margin-top:22px}.section-title{font:600 24px var(--serif);margin:0 0 12px}
    .report-table-wrap{overflow-x:auto}.report-table{width:100%;border-collapse:collapse;text-align:left;font-size:11px}
    .report-table th{padding:10px;color:var(--muted);font-size:9px;text-transform:uppercase}
    .report-table td{padding:12px 10px;border-top:1px solid var(--line);white-space:nowrap}
    @media(max-width:650px){.report-metrics{grid-template-columns:1fr}.report-metrics article{grid-template-columns:1fr auto;align-items:center}}
</style>
@endpush