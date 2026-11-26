<?php
/**
 * Pure-PHP Deterministic Code128B Barcode Vector SVG Generator
 * Requires ZERO PHP GD/Imagick extensions (safe on all cPanel/XAMPP/shared PHP environments).
 */

class SvgBarcode {
    // Standard ISO/IEC 15417 Code 128 patterns (indices 0..106)
    private const PATTERNS = [
        '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
        '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
        '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
        '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
        '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
        '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
        '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
        '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
        '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
        '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
        '114131','311141','411131','211412','211214','211232','2331112'
    ];

    /**
     * Encode text into Code128B module widths & checksum.
     */
    public static function encodeCode128B(string $text): string {
        $clean = preg_replace('/[^\x20-\x7E]/', '', $text);
        if ($clean === '') {
            $clean = '0000';
        }

        $startB = 104;
        $checksum = $startB;
        $codes = [$startB];
        $len = strlen($clean);

        for ($i = 0; $i < $len; $i++) {
            $val = ord($clean[$i]) - 32;
            if ($val < 0 || $val > 95) {
                $val = 0;
            }
            $codes[] = $val;
            $checksum += $val * ($i + 1);
        }

        $codes[] = $checksum % 103;
        $codes[] = 106; // Stop code

        $pattern = '';
        foreach ($codes as $code) {
            $pattern .= self::PATTERNS[$code] ?? '';
        }
        return $pattern;
    }

    /**
     * Render crisp inline SVG barcode for label sheets & product cards.
     */
    public static function render(string $code, int $height = 46, float $barWidth = 1.6, bool $showText = true): string {
        $pattern = self::encodeCode128B($code);
        $totalModules = 0;
        $len = strlen($pattern);
        for ($i = 0; $i < $len; $i++) {
            $totalModules += (int)$pattern[$i];
        }

        $quietZone = 8;
        $svgWidth = round(($totalModules * $barWidth) + ($quietZone * 2), 2);
        $textHeight = $showText ? 16 : 0;
        $svgHeight = $height + $textHeight + 4;

        $x = $quietZone;
        $rects = '';
        for ($i = 0; $i < $len; $i++) {
            $w = (int)$pattern[$i] * $barWidth;
            if ($i % 2 === 0) {
                $rects .= '<rect x="' . round($x, 2) . '" y="2" width="' . round($w, 2) . '" height="' . $height . '" fill="#000000"/>';
            }
            $x += $w;
        }

        $labelSvg = '';
        if ($showText) {
            $safeText = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $centerX = round($svgWidth / 2, 2);
            $textY = $height + 15;
            $labelSvg = '<text x="' . $centerX . '" y="' . $textY . '" text-anchor="middle" font-family="monospace" font-size="11" fill="#111111">' . $safeText . '</text>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $svgWidth . '" height="' . $svgHeight . '" viewBox="0 0 ' . $svgWidth . ' ' . $svgHeight . '" role="img" aria-label="Barcode">' . $rects . $labelSvg . '</svg>';
    }
}
