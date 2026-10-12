<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\DashboardChartSeries;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtisanProductController extends Controller
{
    public function dashboard(Request $request): View
    {
        $products = $request->user()->products();
        $metrics = [
            'all' => (clone $products)->count(),
            'active' => (clone $products)->where('verification_status', Product::STATUS_APPROVED)->count(),
            'pending' => (clone $products)->where('verification_status', Product::STATUS_PENDING)->count(),
            'revision' => (clone $products)->where('verification_status', Product::STATUS_REVISION)->count(),
            'draft' => (clone $products)->where('verification_status', Product::STATUS_DRAFT)->count(),
            'rejected' => (clone $products)->where('verification_status', Product::STATUS_REJECTED)->count(),
            'inactive' => (clone $products)->where('verification_status', Product::STATUS_INACTIVE)->count(),
        ];
        $paidItems = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.artisan_id', $request->user()->id)
            ->where('orders.payment_status', 'paid');
        $paidSales = (clone $paidItems)
            ->selectRaw('coalesce(sum(order_items.unit_price * order_items.quantity), 0) as total_sales, coalesce(sum(order_items.quantity), 0) as units_sold, count(distinct orders.id) as paid_orders')
            ->first();
        $metrics['sales'] = (float) $paidSales->total_sales;
        $metrics['units_sold'] = (int) $paidSales->units_sold;
        $metrics['paid_orders'] = (int) $paidSales->paid_orders;

        $approvedProducts = (clone $products)
            ->where('verification_status', Product::STATUS_APPROVED)
            ->withAvg('reviews', 'product_rating')
            ->get();
        $metrics['approved_rating'] = (float) round($approvedProducts->avg('reviews_avg_product_rating') ?: 0, 1);

        $monthExpression = DashboardChartSeries::monthExpression('orders.paid_at');
        $monthlySalesRows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('sum(order_items.unit_price * order_items.quantity) as total')
            ->where('order_items.artisan_id', $request->user()->id)
            ->where('orders.payment_status', 'paid')
            ->where('orders.paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlySalesSeries = DashboardChartSeries::sixMonths($monthlySalesRows);
        $recentProducts = (clone $products)->latest()->limit(5)->get();

        return view('artisan.dashboard', compact('metrics', 'recentProducts', 'monthlySalesSeries'));
    }

    public function salesReport(Request $request): View
    {
        $paidItems = OrderItem::query()
            ->where('artisan_id', $request->user()->id)
            ->whereHas('order', fn ($query) => $query->where('payment_status', 'paid'));
        $summary = (clone $paidItems)
            ->selectRaw('coalesce(sum(unit_price * quantity), 0) as total_sales, coalesce(sum(quantity), 0) as units_sold, count(distinct order_id) as paid_orders')
            ->first();
        $items = (clone $paidItems)
            ->with(['order.customer', 'product'])
            ->latest('id')
            ->paginate(20);
        $monthExpression = DashboardChartSeries::monthExpression('orders.paid_at');
        $monthlyRows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw('sum(order_items.unit_price * order_items.quantity) as total')
            ->where('order_items.artisan_id', $request->user()->id)
            ->where('orders.payment_status', 'paid')
            ->where('orders.paid_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $monthlySalesSeries = DashboardChartSeries::sixMonths($monthlyRows);

        return view('artisan.sales.index', compact('items', 'summary', 'monthlySalesSeries'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:approved,pending,revision,draft,rejected,inactive'],
        ]);
        $selectedStatus = $filters['status'] ?? null;
        $products = $request->user()->products()
            ->with('category')
            ->withAvg('reviews', 'product_rating')
            ->withCount('reviews')
            ->when($selectedStatus, fn ($query) => $query->where('verification_status', $selectedStatus))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('artisan.products.index', compact('products', 'selectedStatus'));
    }

    public function show(Request $request, Product $product): View
    {
        $this->ensureOwner($request, $product);
        $product->load('category');

        if ($product->verification_status === Product::STATUS_APPROVED) {
            $product->load(['reviews' => fn ($query) => $query->with('customer')->latest()]);
        }

        return view('artisan.products.show', compact('product'));
    }

    public function replyReview(Request $request, Product $product, \App\Models\Review $review): RedirectResponse
    {
        $this->ensureOwner($request, $product);
        abort_unless($product->verification_status === Product::STATUS_APPROVED, 404);
        abort_unless($review->product_id === $product->id, 404);

        $data = $request->validate([
            'artisan_reply' => ['nullable', 'string', 'max:2000'],
        ]);

        $review->update([
            'artisan_reply' => trim((string) ($data['artisan_reply'] ?? '')) === '' ? null : $data['artisan_reply'],
            'replied_at' => now(),
        ]);

        return redirect()->route('artisan.products.show', $product)->with('success', 'Balasan ulasan berhasil dikirim.');
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('artisan.products.form', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request, true);
        $saveAs = $data['save_as'] ?? 'pending';
        $steps = $data['process_steps'] ?? [];
        unset($data['save_as'], $data['process_steps']);
        $images = collect($request->file('images', []))->map(fn ($file) => $file->store('products', 'public'))->all();
        $data['images'] = $images;
        $data['artisan_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['verification_status'] = $saveAs === 'draft' ? Product::STATUS_DRAFT : Product::STATUS_PENDING;
        $data['is_customizable'] = $request->boolean('is_customizable');

        $product = DB::transaction(function () use ($data, $steps) {
            $product = Product::create($data);
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
            return $product;
        });

        $message = $product->verification_status === Product::STATUS_DRAFT
            ? 'Draft karya disimpan.'
            : 'Karya dikirim ke antrean kurator.';

        return redirect()->route('artisan.products.index')->with('success', $message);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->ensureOwner($request, $product);
        $product->load('processSteps');
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('artisan.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureOwner($request, $product);
        $data = $this->validateProduct($request, false, $product);
        $saveAs = $data['save_as'] ?? 'pending';
        $steps = $data['process_steps'] ?? [];
        unset($data['save_as'], $data['process_steps']);

        $existingImages = is_array($product->images) ? $product->images : [];
        $newImages = collect($request->file('images', []))
            ->filter(fn ($file) => $file instanceof \Illuminate\Http\UploadedFile && $file->isValid())
            ->map(fn ($file) => $file->store('products', 'public'))
            ->all();

        $data['images'] = array_values(array_merge($newImages, $existingImages));

        $nextStatus = match ($product->verification_status) {
            Product::STATUS_APPROVED => Product::STATUS_APPROVED,
            default => $saveAs === 'draft' ? Product::STATUS_DRAFT : Product::STATUS_PENDING,
        };

        $data['verification_status'] = $nextStatus;
        $data['verification_note'] = null;
        $data['verified_at'] = $nextStatus === Product::STATUS_APPROVED ? ($product->verified_at ?? now()) : null;
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

        $message = $nextStatus === Product::STATUS_APPROVED
            ? 'Perubahan disimpan dan produk tetap tampil di katalog.'
            : ($saveAs === 'draft'
                ? 'Draft karya disimpan.'
                : 'Perubahan disimpan dan karya dikirim kembali untuk verifikasi.');

        return redirect()->route('artisan.products.index')->with('success', $message);
    }

    private function validateProduct(Request $request, bool $creating, ?Product $product = null): array
    {
        $remainingImageSlots = $creating
            ? 6
            : max(0, 6 - count(is_array($product?->images) ? $product->images : []));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
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
            'images' => [$creating ? 'required' : 'nullable', 'array', 'max:' . $remainingImageSlots],
            'images.*' => ['image', 'max:5120'],
            'process_steps' => ['required', 'array', 'min:1'],
            'process_steps.*.title' => ['required', 'string', 'max:255'],
            'process_steps.*.description' => ['nullable', 'string', 'max:2000'],
            'process_steps.*.duration_minutes' => ['nullable', 'integer', 'min:1'],
            'save_as' => ['nullable', 'in:draft,pending'],
        ]);
    }

    private function ensureOwner(Request $request, Product $product): void
    {
        abort_unless($product->artisan_id === $request->user()->id, 404);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $index = 2;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $index++;
        }
        return $slug;
    }
}
