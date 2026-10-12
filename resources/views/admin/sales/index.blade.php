@extends('layouts.storefront')

@section('title', 'Rekap Penjualan — Admin KaryaLokal')

@section('content')
<section class="shell sales-report">
    <a class="muted" style="font-size:11px" href="{{ route('admin.dashboard') }}">← Dashboard admin</a>
    <div class="eyebrow" style="margin-top:20px">Transaksi terverifikasi</div>
    <h1 class="page-title">Rekap penjualan</h1>
    <div class="report-metrics">
        <article><span>Total transaksi dibayar</span><strong>Rp{{ number_format((float) $totalSales, 0, ',', '.') }}</strong></article>
        <article><span>Order dibayar</span><strong>{{ number_format($paidOrderCount) }}</strong></article>
        <article><span>Rata-rata nilai order</span><strong>Rp{{ number_format($paidOrderCount ? $totalSales / $paidOrderCount : 0, 0, ',', '.') }}</strong></article>
    </div>
    <x-dashboard-chart title="Penjualan per bulan" :data="$monthlySalesSeries" type="line" color="#397cf6" />
    <section class="report-section">
        <h2 class="section-title">Order dibayar</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Tanggal bayar</th><th>Metode</th><th>Total</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->customer->name }}</td>
                            <td>{{ $order->paid_at?->format('d M Y') ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                            <td>Rp{{ number_format((float) $order->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">Belum ada order yang dibayar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $orders->links() }}</div>
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
    .report-table td a{color:var(--green);font-weight:700}
    @media(max-width:650px){.report-metrics{grid-template-columns:1fr}.report-metrics article{grid-template-columns:1fr auto;align-items:center}}
</style>
@endpush