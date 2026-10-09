<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;

/**
 * One place that knows how every Arabic (RTL) PDF of the academy is built, so
 * the teacher's exam paper and the student's result sheet can never drift
 * apart: mPDF with native RTL + Arabic shaping, A4, same margins and font.
 */
class ArabicMpdf
{
    public const BRAND_AR = 'أكاديمية العلامة الكاملة';

    public static function make(string $title): Mpdf
    {
        $tmp = storage_path('app/mpdf');
        File::ensureDirectoryExists($tmp);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => $tmp,
            'default_font' => 'dejavusans',
            'margin_left' => 16,
            'margin_right' => 16,
            'margin_top' => 14,
            'margin_bottom' => 18,
            'margin_footer' => 8,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle($title);
        $mpdf->SetAuthor(self::BRAND_AR);

        return $mpdf;
    }

    /** Absolute path of the letterhead logo, or null when the file is missing. */
    public static function logoPath(): ?string
    {
        $logo = public_path('site/images/logo_v2_blue.png');

        return is_file($logo) ? $logo : null;
    }

    /** Arabic-Indic digits, e.g. 25 -> ٢٥ (matches the numbering on the printed paper). */
    public static function digits(int|float|string|null $value): string
    {
        return strtr((string) $value, ['0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩']);
    }
}
