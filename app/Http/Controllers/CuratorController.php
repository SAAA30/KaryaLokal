<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CraftQrCodeService;
use App\Support\DashboardChartSeries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CuratorController extends Controller
{
    public function dashboard(): View
    {
        $metrics = [
            'pending' => Product::where('verification_status', Product::STATUS_PENDING)->count(),
            'approved' => Product::where('verification_status', Product::STATUS_APPROVED)->count(),
            'revision' => Product::where('verification_status', Product::STATUS_REVISION)->count(),
            'rejected' => Product::where('verification_status', Product::STATUS_REJECTED)->count(),
        ];
        $queue = Product::with(['artisan', 'category'])
            ->whereIn('verification_status', [Product::STATUS_PENDING, Product::STATUS_REVISION])
            ->oldest()
            ->limit(8)
            ->get();
        $monthExpression = DashboardChartSeries::monthExpression();
        $monthlyProductRows = Product::query()
            ->selectRaw("{$monthExpression} as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlyProductSeries = DashboardChartSeries::sixMonths($monthlyProductRows);

        return view('curator.dashboard', compact('metrics', 'queue', 'monthlyProductSeries'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,revision,rejected'],
        ]);
        $selectedStatus = $filters['status'] ?? null;
        $products = Product::with(['artisan', 'category'])
            ->when($selectedStatus, fn ($query) => $query->where('verification_status', $selectedStatus))
            ->when(!$selectedStatus, fn ($query) => $query->whereIn('verification_status', [Product::STATUS_PENDING, Product::STATUS_REVISION]))
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        return view('curator.products.index', compact('products', 'selectedStatus'));
    }

    public function show(Product $product): View
    {
        $product->load(['artisan.artisanProfile', 'category', 'processSteps']);

        if ($product->verification_status !== Product::STATUS_PENDING) {
            return view('curator.products.status-show', compact('product'));
        }

        return view('curator.products.show', compact('product'));
    }

    public function decide(Request $request, Product $product, CraftQrCodeService $qrCode): RedirectResponse
    {
        if ($product->verification_status !== Product::STATUS_PENDING) {
            return back()->with('error', 'Karya ini tidak lagi menunggu verifikasi.');
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approved,revision,rejected'],
            'note' => ['nullable', 'string', 'max:2000'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['boolean'],
        ]);

        if ($data['decision'] === Product::STATUS_APPROVED) {
            $checks = ['photos', 'story', 'materials', 'artisan_origin', 'process', 'originality'];
            foreach ($checks as $check) {
                if (!filter_var($data['checklist'][$check] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    throw ValidationException::withMessages(['checklist' => 'Checklist keaslian dan kelayakan harus lengkap sebelum menyetujui karya.']);
                }
            }
        } elseif (blank($data['note'] ?? null)) {
            throw ValidationException::withMessages(['note' => 'Catatan wajib diisi untuk revisi atau penolakan.']);
        }

        DB::transaction(function () use ($product, $data) {
            $product->update([
                'verification_status' => $data['decision'],
                'verification_note' => $data['note'] ?? null,
                'verified_at' => $data['decision'] === Product::STATUS_APPROVED ? now() : null,
                'qr_active' => false,
            ]);
        });

        if ($data['decision'] === Product::STATUS_APPROVED) {
            $qrCode->issue($product->fresh());
        }

        $message = match ($data['decision']) {
            Product::STATUS_APPROVED => 'Karya disetujui dan Craft ID/QR telah dibuat.',
            Product::STATUS_REVISION => 'Karya dikembalikan kepada perajin untuk revisi.',
            default => 'Karya ditolak dengan catatan kurator.',
        };

        return redirect()->route('curator.products.index')->with('success', $message);
    }
}
