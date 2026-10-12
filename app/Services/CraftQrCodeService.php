<?php

namespace App\Services;

use App\Models\Product;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

class CraftQrCodeService
{
    public function issue(Product $product): string
    {
        $craftId = $product->craft_id ?: $this->newCraftId();
        $url = route('craft.story', ['craftId' => $craftId]);
        $code = QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(480)
            ->setMargin(16)
            ->setForegroundColor(new Color(24, 53, 45))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = (new SvgWriter())->write($code);
        $path = "craft-qr/{$craftId}.svg";
        Storage::disk('public')->put($path, $result->getString());

        $product->forceFill([
            'craft_id' => $craftId,
            'qr_active' => true,
        ])->save();

        return $path;
    }

    private function newCraftId(): string
    {
        do {
            $craftId = 'KL-' . now()->format('Y') . '-' . random_int(100000, 999999);
        } while (Product::where('craft_id', $craftId)->exists());

        return $craftId;
    }
}
