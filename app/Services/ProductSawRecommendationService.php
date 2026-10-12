<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductSawRecommendationService
{
    public const CRITERIA = [
        'price' => ['label' => 'Harga', 'type' => 'Cost', 'weight' => 0.30],
        'sold_units' => ['label' => 'Unit terjual', 'type' => 'Benefit', 'weight' => 0.30],
        'rating' => ['label' => 'Rating pembeli', 'type' => 'Benefit', 'weight' => 0.20],
        'stock' => ['label' => 'Stok tersedia', 'type' => 'Benefit', 'weight' => 0.20],
    ];

    public function rankedProducts(): Collection
    {
        $products = Product::query()
            ->with(['artisan', 'category'])
            ->withAvg(['reviews as saw_average_rating' => fn ($query) => $query->where('is_published', true)], 'product_rating')
            ->where('verification_status', Product::STATUS_APPROVED)
            ->get();

        if ($products->isEmpty()) {
            return collect();
        }

        $paidUnits = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid')
            ->select('order_items.product_id')
            ->selectRaw('sum(order_items.quantity) as sold_units')
            ->groupBy('order_items.product_id')
            ->pluck('sold_units', 'product_id');

        $minimumPrice = (float) $products->min(fn (Product $product) => (float) $product->price);
        $maximumSales = max(0, (int) $paidUnits->max());
        $maximumStock = max(0, (int) $products->max('stock'));

        return $products
            ->map(function (Product $product) use ($paidUnits, $minimumPrice, $maximumSales, $maximumStock) {
                $price = (float) $product->price;
                $soldUnits = (int) ($paidUnits[$product->id] ?? 0);
                $rating = min(5, max(0, (float) ($product->saw_average_rating ?? 0)));
                $stock = max(0, (int) $product->stock);
                $normalized = [
                    'price' => $price <= 0 ? 1.0 : $minimumPrice / $price,
                    'sold_units' => $maximumSales > 0 ? $soldUnits / $maximumSales : 0.0,
                    'rating' => $rating / 5,
                    'stock' => $maximumStock > 0 ? $stock / $maximumStock : 0.0,
                ];
                $score = 0.0;
                foreach (self::CRITERIA as $key => $criterion) {
                    $score += $normalized[$key] * $criterion['weight'];
                }

                return [
                    'product' => $product,
                    'price' => $price,
                    'sold_units' => $soldUnits,
                    'rating' => $rating,
                    'stock' => $stock,
                    'normalized' => $normalized,
                    'score' => round($score, 4),
                    'priority' => $this->priority($score),
                ];
            })
            ->sort(function (array $left, array $right) {
                return ($right['score'] <=> $left['score'])
                    ?: ($right['sold_units'] <=> $left['sold_units'])
                    ?: ($left['price'] <=> $right['price'])
                    ?: strcasecmp($left['product']->name, $right['product']->name);
            })
            ->values()
            ->map(function (array $row, int $index) {
                $row['rank'] = $index + 1;

                return $row;
            });
    }

    public function priority(float $score): string
    {
        return match (true) {
            $score >= 0.8 => 'Prioritas Tinggi',
            $score >= 0.6 => 'Normal',
            default => 'Evaluasi',
        };
    }
}