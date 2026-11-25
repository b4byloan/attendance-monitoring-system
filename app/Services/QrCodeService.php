<?php

namespace App\Services;

use App\Models\Setting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;

class QrCodeService
{
    public function build(string $data, ?int $size = null): ResultInterface
    {
        $size = $size ?? (int) Setting::current()->qr_default_size;

        // The QR spec requires a quiet zone of four modules around the code,
        // but endroid takes the margin in pixels. Render once to read the
        // module count, then keep the requested size as the whole image so
        // modules stay square and are never resampled.
        $blockCount = $this->builder($data, $size, 0)->build()->getMatrix()->getBlockCount();
        $blockSize = max(1, intdiv($size, $blockCount + 8));

        return $this->builder($data, $blockSize * $blockCount, 4 * $blockSize)->build();
    }

    public function dataUri(string $data, ?int $size = null): string
    {
        return $this->build($data, $size)->getDataUri();
    }

    private function builder(string $data, int $size, int $margin): Builder
    {
        return new Builder(
            writer: new PngWriter,
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
        );
    }
}
