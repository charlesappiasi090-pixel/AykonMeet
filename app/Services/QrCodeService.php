<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Collection;
use RuntimeException;

class QrCodeService
{
    public function hydrateAttendees(Collection $attendees, string $payloadPrefix): void
    {
        $attendees->each(function ($attendee) use ($payloadPrefix) {
            $attendee->qr_svg = $this->svg($payloadPrefix.'?code='.$attendee->attendee_code, 5);
        });
    }

    public function svg(string $content, int $scale = 6): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'quietzoneSize' => 2,
            'svgAddXmlHeader' => false,
            'outputBase64' => false,
            'svgUseFillAttributes' => true,
        ]);

        try {
            return (new QRCode($options))->render($content);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Could not generate QR code: '.$exception->getMessage());
        }
    }

    public function dataUri(string $content, int $scale = 6): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'quietzoneSize' => 2,
            'svgAddXmlHeader' => false,
            'outputBase64' => true,
            'svgUseFillAttributes' => true,
        ]);

        try {
            return (new QRCode($options))->render($content);
        } catch (\Throwable $exception) {
            throw new RuntimeException('Could not generate QR code: '.$exception->getMessage());
        }
    }
}
