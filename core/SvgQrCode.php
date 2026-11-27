<?php
/**
 * UPI Deep Link URI Builder & Dynamic QR Render Helper
 * Clearly generates standard NPCI/UPI intents without claiming automated payment verification.
 */

class SvgQrCode {
    /**
     * Build standard NPCI UPI intent URL: upi://pay?pa=...&pn=...&am=...&cu=INR&tn=...
     */
    public static function buildUpiUri(string $upiId, string $payeeName, float $amount = 0.0, string $note = ''): string {
        $pa = trim($upiId);
        if ($pa === '') {
            return '';
        }
        $params = [
            'pa' => $pa,
            'pn' => trim($payeeName) !== '' ? mb_substr(trim($payeeName), 0, 50) : 'Merchant'
        ];
        if ($amount > 0) {
            $params['am'] = number_format($amount, 2, '.', '');
        }
        $params['cu'] = 'INR';
        if (trim($note) !== '') {
            $params['tn'] = mb_substr(trim($note), 0, 50);
        }

        return 'upi://pay?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Render dynamic QR widget markup hydrated synchronously by local MIT qrcode-generator JS.
     */
    public static function renderUpiWidget(array $firm, float $amount, string $invoiceNumber, int $size = 110): string {
        $upiId = trim((string)($firm['upi_id'] ?? ''));
        if ($upiId === '') {
            return '';
        }
        $uri = self::buildUpiUri($upiId, (string)($firm['name'] ?? 'Business'), $amount, "Bill #{$invoiceNumber}");
        $safeUri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8');
        $safeUpiId = htmlspecialchars($upiId, ENT_QUOTES, 'UTF-8');

        return '<div class="upi-qr-box text-center d-inline-block">'
            . '<div class="upi-qr-render mx-auto" data-upi-uri="' . $safeUri . '" data-qr-size="' . (int)$size . '" style="width:' . (int)$size . 'px;height:' . (int)$size . 'px;"></div>'
            . '<div class="small text-muted font-monospace mt-1" style="font-size:10px;">' . $safeUpiId . '</div>'
            . '</div>';
    }
}
