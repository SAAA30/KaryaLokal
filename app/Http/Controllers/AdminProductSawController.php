<?php

namespace App\Http\Controllers;

use App\Services\ProductSawRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductSawController extends Controller
{
    public function index(Request $request, ProductSawRecommendationService $recommendations): View
    {
        $ranking = $recommendations->rankedProducts();
        $search = trim($request->string('q')->toString());
        $searchTerm = Str::lower($search);
        $visibleRanking = $ranking->filter(function (array $row) use ($searchTerm) {
            if ($searchTerm === '') {
                return true;
            }

            $product = $row['product'];
            $searchable = Str::lower(implode(' ', [
                $product->name,
                $product->artisan?->name,
                $product->category?->name,
                $product->material,
            ]));

            return Str::contains($searchable, $searchTerm);
        })->values();

        $priorities = collect(['Prioritas Tinggi', 'Normal', 'Evaluasi'])
            ->mapWithKeys(fn (string $priority) => [$priority => $ranking->where('priority', $priority)->count()]);
        $topProducts = $ranking->take(10);
        $maximumPrice = max(1, (float) $ranking->max('price'));
        $maximumSales = max(1, (int) $ranking->max('sold_units'));
        $scatterPoints = $ranking->map(fn (array $row) => [
            'name' => $row['product']->name,
            'price' => $row['price'],
            'sold_units' => $row['sold_units'],
            'left' => min(100, $row['price'] / $maximumPrice * 100),
            'bottom' => min(100, $row['sold_units'] / $maximumSales * 100),
            'priority' => $row['priority'],
        ]);
        $priorityTotal = max(1, $priorities->sum());
        $highPercent = $priorities['Prioritas Tinggi'] / $priorityTotal * 100;
        $normalPercent = $highPercent + ($priorities['Normal'] / $priorityTotal * 100);

        return view('admin.saw.index', compact(
            'ranking',
            'visibleRanking',
            'search',
            'priorities',
            'topProducts',
            'scatterPoints',
            'maximumPrice',
            'maximumSales',
            'highPercent',
            'normalPercent'
        ));
    }
}