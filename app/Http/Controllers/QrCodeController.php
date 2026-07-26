<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    private function logoPath(): string
    {
        return public_path('site/images/logo_v2_gold.png');
    }

    /**
     * Renders as SVG (no Imagick/GD backend required — works on any shared host)
     * and embeds the academy logo directly into the SVG markup, so the logo is
     * baked into the file whether it's viewed inline or downloaded.
     */
    private function render(string $url): Response
    {
        $size = 500;

        $svg = QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        $logoSize = (int) round($size * 0.2);
        $padding = 8;
        $bgSize = $logoSize + $padding * 2;
        $bgPos = ($size - $bgSize) / 2;
        $logoPos = ($size - $logoSize) / 2;
        $logoBase64 = base64_encode(file_get_contents($this->logoPath()));

        $overlay = sprintf(
            '<rect x="%1$s" y="%1$s" width="%2$s" height="%2$s" rx="10" fill="#ffffff"/><image x="%3$s" y="%3$s" width="%4$s" height="%4$s" href="data:image/png;base64,%5$s"/>',
            $bgPos,
            $bgSize,
            $logoPos,
            $logoSize,
            $logoBase64
        );

        $svg = str_replace('</svg>', $overlay . '</svg>', $svg);

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * QR code pointing to a subject's card on its program page.
     */
    public function subject(Subject $subject): Response
    {
        $subject->loadMissing('program');
        $url = route('programs.show', $subject->program->slug) . '#subject-' . $subject->id;

        return $this->render($url);
    }

    /**
     * QR code pointing to the admin control panel's login page.
     */
    public function admin(): Response
    {
        return $this->render(route('admin.login'));
    }
}
