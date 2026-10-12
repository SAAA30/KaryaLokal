<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\QrScan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\CraftQrCodeService;
use App\Support\DashboardChartSeries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceAdminController extends Controller
{
    public function index(): View
    {
        $paidOrders = Order::where('payment_status', 'paid');
        $metrics = [
            'users' => User::count(),
            'artisans' => User::where('role', User::ROLE_ARTISAN)->count(),
            'customers' => User::where('role', User::ROLE_CUSTOMER)->count(),
            'products' => Product::count(),
            'pending_products' => Product::where('verification_status', Product::STATUS_PENDING)->count(),
            'orders' => Order::count(),
            'sales' => (clone $paidOrders)->sum('total'),
            'paid_orders' => (clone $paidOrders)->count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count(),
            'qr_codes' => Product::where('qr_active', true)->count(),
            'qr_scans' => QrScan::count(),
        ];
        $metrics['average_paid_order'] = $metrics['paid_orders'] > 0
            ? $metrics['sales'] / $metrics['paid_orders']
            : 0;
        $popularProducts = Product::with('artisan')->where('qr_active', true)->orderByDesc('scan_count')->limit(5)->get();
        $recentOrders = Order::with('customer')->latest()->limit(8)->get();
        $pendingProducts = Product::with('artisan')
            ->where('verification_status', Product::STATUS_PENDING)
            ->latest()
            ->limit(5)
            ->get();
        $openTickets = SupportTicket::with('customer')
            ->whereIn('status', ['open', 'in_progress', 'waiting_customer'])
            ->latest()
            ->limit(5)
            ->get();
        $artisanSales = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('users', 'order_items.artisan_id', '=', 'users.id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('users.id as artisan_id, users.name as artisan_name, sum(order_items.unit_price * order_items.quantity) as product_sales, sum(order_items.quantity) as units_sold, count(distinct orders.id) as paid_orders')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('product_sales')
            ->limit(8)
            ->get();
        $monthExpression = DashboardChartSeries::monthExpression();
        $periodStart = now()->subMonths(5)->startOfMonth();
        $monthlyOrders = Order::selectRaw("{$monthExpression} as month, count(*) as total")
            ->where('created_at', '>=', $periodStart)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $salesMonthExpression = DashboardChartSeries::monthExpression('paid_at');
        $monthlySales = Order::selectRaw("{$salesMonthExpression} as month, sum(total) as total")
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', $periodStart)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlyOrderSeries = DashboardChartSeries::sixMonths($monthlyOrders);
        $monthlySalesSeries = DashboardChartSeries::sixMonths($monthlySales);

        return view('admin.marketplace-dashboard', compact('metrics', 'popularProducts', 'recentOrders', 'pendingProducts', 'openTickets', 'monthlyOrderSeries', 'monthlySalesSeries', 'artisanSales'));
    }

    public function orderShow(Order $order): View
    {
        $order->load(['customer', 'items.artisan']);

        return view('admin.orders.show', compact('order'));
    }

    public function pendingProductsIndex(): View
    {
        $products = Product::with(['artisan', 'category'])
            ->where('verification_status', Product::STATUS_PENDING)
            ->oldest()
            ->paginate(10);

        return view('admin.pending-products.index', compact('products'));
    }

    public function pendingProductShow(Product $product): View
    {
        abort_unless($product->verification_status === Product::STATUS_PENDING, 404);
        $product->load(['artisan.artisanProfile', 'category', 'processSteps']);

        return view('admin.pending-products.show', compact('product'));
    }

    public function showProduct(Product $product): View
    {
        abort_unless(in_array($product->verification_status, [
            Product::STATUS_APPROVED,
            Product::STATUS_PENDING,
            Product::STATUS_REVISION,
        ], true), 404);

        $product->load(['artisan.artisanProfile', 'category', 'processSteps', 'reviews.customer']);

        return view('admin.products.show', compact('product'));
    }

    public function editProduct(Product $product): View
    {
        abort_unless($product->verification_status === Product::STATUS_APPROVED || $product->verification_status === Product::STATUS_PENDING || $product->verification_status === Product::STATUS_REVISION, 404);
        $product->load('processSteps');
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $backRoute = request()->query('return_to', $product->verification_status === Product::STATUS_APPROVED ? route('storefront.catalog') : route('admin.pending-products.index'));

        return view('artisan.products.form', [
            'product' => $product,
            'categories' => $categories,
            'backRoute' => $backRoute,
            'submitRoute' => route('admin.products.update', $product),
            'isAdminEdit' => true,
        ]);
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $existingImages = is_array($product->images) ? $product->images : [];
        $remainingImageSlots = max(0, 6 - count($existingImages));
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:100', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($product->id)],
            'description' => ['required', 'string', 'max:5000'],
            'story' => ['required', 'string', 'max:10000'],
            'material' => ['required', 'string', 'max:255'],
            'technique' => ['required', 'string', 'max:255'],
            'origin' => ['required', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'weight_grams' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'fulfillment_type' => ['required', 'in:ready_stock,pre_order'],
            'production_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'is_customizable' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:' . $remainingImageSlots],
            'images.*' => ['image', 'max:5120'],
            'process_steps' => ['required', 'array', 'min:1'],
            'process_steps.*.title' => ['required', 'string', 'max:255'],
            'process_steps.*.description' => ['nullable', 'string', 'max:2000'],
            'process_steps.*.duration_minutes' => ['nullable', 'integer', 'min:1'],
        ]);

        $steps = $data['process_steps'] ?? [];
        unset($data['process_steps']);

        $newImages = collect($request->file('images', []))
            ->filter(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile && $file->isValid())
            ->map(fn ($file) => $file->store('products', 'public'))
            ->all();

        $data['images'] = array_values(array_merge($newImages, $existingImages));

        $nextStatus = $product->verification_status === Product::STATUS_APPROVED ? Product::STATUS_APPROVED : $product->verification_status;
        $data['verification_status'] = $nextStatus;
        $data['verification_note'] = null;
        $data['verified_at'] = $nextStatus === Product::STATUS_APPROVED ? ($product->verified_at ?? now()) : $product->verified_at;
        $data['qr_active'] = $nextStatus === Product::STATUS_APPROVED ? (bool) $product->qr_active : false;
        $data['is_customizable'] = $request->boolean('is_customizable');

        DB::transaction(function () use ($product, $data, $steps) {
            $product->update($data);
            $product->processSteps()->delete();
            foreach ($steps as $index => $step) {
                if (filled($step['title'] ?? null)) {
                    $product->processSteps()->create([
                        'step_number' => $index + 1,
                        'title' => $step['title'],
                        'description' => $step['description'] ?? null,
                        'duration_minutes' => $step['duration_minutes'] ?? null,
                    ]);
                }
            }
        });

        $redirectRoute = $request->input('return_to', $request->query('return_to', $nextStatus === Product::STATUS_APPROVED ? route('storefront.catalog') : route('admin.pending-products.index')));

        return redirect()->to($redirectRoute)->with('success', $nextStatus === Product::STATUS_APPROVED
            ? 'Perubahan disimpan dan status karya tetap approved.'
            : 'Perubahan karya berhasil disimpan.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        if ($order->payment_status !== 'paid' || !in_array($order->status, ['processing', 'shipped'], true)) {
            return back()->with('error', 'Nomor resi hanya dapat diperbarui setelah pembayaran terverifikasi.');
        }

        $data = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
        ]);

        $order->update([
            'tracking_number' => trim($data['tracking_number']),
            'status' => 'shipped',
        ]);

        return back()->with('success', 'Nomor resi berhasil disimpan dan pesanan ditandai telah dikirim.');
    }

    public function salesReport(): View
    {
        $paidOrders = Order::with('customer')->where('payment_status', 'paid');
        $totalSales = (clone $paidOrders)->sum('total');
        $paidOrderCount = (clone $paidOrders)->count();
        $orders = (clone $paidOrders)->latest('paid_at')->paginate(20);
        $monthExpression = DashboardChartSeries::monthExpression('paid_at');
        $monthlyRows = Order::query()
            ->selectRaw("{$monthExpression} as month, sum(total) as total")
            ->where('payment_status', 'paid')
            ->where('paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlySalesSeries = DashboardChartSeries::sixMonths($monthlyRows);

        return view('admin.sales.index', compact('orders', 'totalSales', 'paidOrderCount', 'monthlySalesSeries'));
    }

    public function qrIndex(): View
    {
        $products = Product::with(['artisan', 'category'])
            ->whereNotNull('craft_id')
            ->latest()
            ->paginate(20);

        return view('admin.qr-index', compact('products'));
    }

    public function toggleQr(Product $product, Request $request, CraftQrCodeService $qrCode): RedirectResponse
    {
        abort_unless($product->verification_status === Product::STATUS_APPROVED && $product->craft_id, 404);

        if ($product->qr_active) {
            $product->update(['qr_active' => false]);
            return back()->with('success', 'QR dinonaktifkan.');
        }

        $qrCode->issue($product);

        return back()->with('success', 'QR diaktifkan kembali.');
    }

    public function regenerateQr(Product $product, Request $request, CraftQrCodeService $qrCode): RedirectResponse
    {
        abort_unless($product->verification_status === Product::STATUS_APPROVED && $product->craft_id, 404);
        $qrCode->issue($product);

        return back()->with('success', 'File QR dibuat ulang dengan Craft ID yang sama.');
    }
}
