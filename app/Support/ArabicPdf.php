<?php

namespace App\Support;

use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

class ArabicPdf
{
    /**
     * Render a Blade view to a DomPDF instance with Arabic glyph shaping.
     */
    public static function loadView(string $view, array $data = [], string $paper = 'a4', string $orientation = 'portrait'): DomPdf
    {
        $html = view($view, $data)->render();

        return Pdf::loadHTML(self::shapeArabicHtml($html))
            ->setPaper($paper, $orientation)
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');
    }

    /**
     * Shape Arabic segments so DomPDF renders connected RTL glyphs correctly.
     */
    public static function shapeArabicHtml(string $html): string
    {
        $arabic = new Arabic();
        $matches = $arabic->arIdentify($html);

        for ($i = count($matches) - 1; $i >= 0; $i -= 2) {
            $start = $matches[$i - 1];
            $end = $matches[$i];
            $segment = substr($html, $start, $end - $start);
            $shaped = $arabic->utf8Glyphs($segment);
            $html = substr_replace($html, $shaped, $start, $end - $start);
        }

        return $html;
    }
}
