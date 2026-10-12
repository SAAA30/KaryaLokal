<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class PaymentQrCodeService
{
    public function checkoutImage(): ?string
    {
        if (!config('services.karyalokal_payment.simulation')) {
            return null;
        }

        $qrCode = QrCode::create(config('services.karyalokal_payment.qris_simulation_payload'))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(320)
            ->setMargin(12)
            ->setForegroundColor(new Color(20, 25, 30))
            ->setBackgroundColor(new Color(255, 255, 255));

        $svg = (new SvgWriter())->write($qrCode)->getString();

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}