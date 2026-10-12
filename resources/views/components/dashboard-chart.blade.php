@props(['title', 'data', 'type' => 'bar', 'color' => '#6366f1'])

@php
    $points = collect($data)->values();
    $maximum = max(1, (float) $points->max('value'));
    $plotLeft = 58;
    $plotTop = 16;
    $plotWidth = 530;
    $plotHeight = 142;
    $plotBottom = $plotTop + $plotHeight;
    $bandWidth = $plotWidth / max(1, $points->count());
    $pointCoordinates = $points->map(function ($point, $index) use ($points, $plotLeft, $plotTop, $plotWidth, $plotHeight, $maximum) {
        $x = $plotLeft + ($points->count() > 1 ? $plotWidth * $index / ($points->count() - 1) : $plotWidth / 2);
        $y = $plotTop + $plotHeight - ((float) $point['value'] / $maximum * $plotHeight);

        return ['x' => $x, 'y' => $y];
    })->all();
    $linePath = '';
    if ($pointCoordinates !== []) {
        $firstPoint = $pointCoordinates[0];
        $linePath = "M {$firstPoint['x']} {$firstPoint['y']}";
        for ($index = 1; $index < count($pointCoordinates); $index++) {
            $previousPoint = $pointCoordinates[$index - 1];
            $currentPoint = $pointCoordinates[$index];
            $controlOffset = ($currentPoint['x'] - $previousPoint['x']) / 3;
            $linePath .= sprintf(
                ' C %.2f %.2f, %.2f %.2f, %.2f %.2f',
                $previousPoint['x'] + $controlOffset,
                $previousPoint['y'],
                $currentPoint['x'] - $controlOffset,
                $currentPoint['y'],
                $currentPoint['x'],
                $currentPoint['y']
            );
        }
        $lastPoint = $pointCoordinates[count($pointCoordinates) - 1];
        $areaPath = $linePath . " L {$lastPoint['x']} {$plotBottom} L {$firstPoint['x']} {$plotBottom} Z";
    }
@endphp

<section class="dashboard-chart-panel">
    <div class="dashboard-chart-heading">
        <h2>{{ $title }}</h2>
    </div>
    @if($points->isEmpty())
        <p class="muted" style="font-size:11px;margin:16px 0 4px">Belum ada data untuk ditampilkan.</p>
    @else
        <svg class="dashboard-chart-svg" viewBox="0 0 640 218" role="img" aria-label="{{ $title }}">
            @foreach([0, 0.25, 0.5, 0.75, 1] as $gridFraction)
                @php($gridY = $plotTop + $plotHeight * $gridFraction)
                <line x1="{{ $plotLeft }}" y1="{{ $gridY }}" x2="{{ $plotLeft + $plotWidth }}" y2="{{ $gridY }}" class="chart-gridline" />
                <text x="{{ $plotLeft - 9 }}" y="{{ $gridY + 4 }}" text-anchor="end" class="chart-tick">{{ number_format((int) round($maximum * (1 - $gridFraction))) }}</text>
            @endforeach
            @if($type === 'bar')
                @foreach($points as $index => $point)
                    @php($barWidth = min(48, $bandWidth * 0.56))
                    @php($barHeight = (float) $point['value'] / $maximum * $plotHeight)
                    @php($barX = $plotLeft + $bandWidth * $index + ($bandWidth - $barWidth) / 2)
                    @php($barY = $plotBottom - $barHeight)
                    <rect x="{{ $barX }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ max(0, $barHeight) }}" rx="2" fill="{{ $color }}" />
                    <text x="{{ $barX + $barWidth / 2 }}" y="{{ max(12, $barY - 5) }}" text-anchor="middle" class="chart-value">{{ number_format((float) $point['value']) }}</text>
                    <text x="{{ $barX + $barWidth / 2 }}" y="{{ $plotBottom + 25 }}" text-anchor="middle" class="chart-label">{{ $point['label'] }}</text>
                @endforeach
            @else
                <path d="{{ $areaPath }}" fill="{{ $color }}" fill-opacity="0.12" />
                <path d="{{ $linePath }}" fill="none" stroke="{{ $color }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                @foreach($points as $index => $point)
                    @php($pointX = $pointCoordinates[$index]['x'])
                    @php($pointY = $pointCoordinates[$index]['y'])
                    <circle cx="{{ $pointX }}" cy="{{ $pointY }}" r="4" fill="{{ $color }}" stroke="#fff" stroke-width="2" />
                    <text x="{{ $pointX }}" y="{{ max(12, $pointY - 9) }}" text-anchor="middle" class="chart-value">{{ number_format((float) $point['value']) }}</text>
                    <text x="{{ $pointX }}" y="{{ $plotBottom + 25 }}" text-anchor="middle" class="chart-label">{{ $point['label'] }}</text>
                @endforeach
            @endif
            <line x1="{{ $plotLeft }}" y1="{{ $plotBottom }}" x2="{{ $plotLeft + $plotWidth }}" y2="{{ $plotBottom }}" class="chart-axis" />
        </svg>
    @endif
</section>

@once
    @push('styles')
    <style>
        .dashboard-chart-panel{min-width:0;padding:16px;background:var(--white);border:1px solid var(--line)}
        .dashboard-chart-heading{display:flex;justify-content:space-between;align-items:baseline;gap:10px}
        .dashboard-chart-heading h2{margin:0;font:600 17px var(--serif)}
        .dashboard-chart-svg{display:block;width:100%;height:auto;margin-top:12px;overflow:visible}
        .chart-gridline{stroke:#d9dce3;stroke-width:1}
        .chart-axis{stroke:var(--muted);stroke-width:1}
        .chart-value{fill:var(--ink);font:600 10px var(--sans)}
        .chart-label,.chart-tick{fill:var(--muted);font:10px var(--sans)}
    </style>
    @endpush
@endonce