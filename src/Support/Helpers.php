<?php

namespace SantosDave\Paystack\Support;

class Helpers
{
    /**
     * Generate a unique reference.
     *
     * @param string $prefix
     * @return string
     */
    public static function generateReference(string $prefix = 'PS'): string
    {
        return $prefix . '_' . time() . '_' . bin2hex(random_bytes(8));
    }

    /**
     * Format amount for display.
     *
     * @param int $amount Amount in subunits
     * @param string $currency
     * @return string
     */
    public static function formatAmount(int $amount, string $currency = 'NGN'): string
    {
        $mainAmount = $amount / 100;

        $symbols = [
            'NGN' => '₦',
            'USD' => '$',
            'GHS' => '₵',
            'ZAR' => 'R',
            'KES' => 'Ksh.',
            'XOF' => 'XOF',
        ];

        $symbol = $symbols[$currency] ?? $currency;

        return $symbol . ' ' . number_format($mainAmount, 2);
    }

    /**
     * Get supported currencies.
     *
     * @return array
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'NGN' => ['name' => 'Nigerian Naira', 'symbol' => '₦', 'minimum' => 50],
            'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'minimum' => 2],
            'GHS' => ['name' => 'Ghanaian Cedi', 'symbol' => '₵', 'minimum' => 0.10],
            'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'minimum' => 1],
            'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'Ksh.', 'minimum' => 3],
            'XOF' => ['name' => 'West African CFA Franc', 'symbol' => 'XOF', 'minimum' => 1],
        ];
    }

    /**
     * Get currency minimum amount.
     *
     * @param string $currency
     * @return float
     */
    public static function getCurrencyMinimum(string $currency): float
    {
        $currencies = self::getSupportedCurrencies();
        return $currencies[$currency]['minimum'] ?? 0;
    }

    /**
     * Validate amount meets minimum requirement.
     *
     * @param float $amount
     * @param string $currency
     * @return bool
     */
    public static function validateAmount(float $amount, string $currency): bool
    {
        $minimum = self::getCurrencyMinimum($currency);
        return $amount >= $minimum;
    }

    /**
     * Get webhook event types.
     *
     * @return array
     */
    public static function getWebhookEventTypes(): array
    {
        return [
            'charge.success',
            'charge.dispute.create',
            'charge.dispute.remind',
            'charge.dispute.resolve',
            'customeridentification.failed',
            'customeridentification.success',
            'invoice.create',
            'invoice.payment_failed',
            'invoice.update',
            'paymentrequest.pending',
            'paymentrequest.success',
            'refund.failed',
            'refund.pending',
            'refund.processed',
            'refund.processing',
            'subscription.create',
            'subscription.disable',
            'subscription.expiring_cards',
            'subscription.not_renew',
            'transfer.failed',
            'transfer.reversed',
            'transfer.success',
        ];
    }

    /**
     * Sanitize metadata.
     *
     * @param array $metadata
     * @return array
     */
    public static function sanitizeMetadata(array $metadata): array
    {
        // Remove null values and limit depth
        return array_filter($metadata, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Build callback URL with parameters.
     *
     * @param string $baseUrl
     * @param array $params
     * @return string
     */
    public static function buildCallbackUrl(string $baseUrl, array $params = []): string
    {
        if (empty($params)) {
            return $baseUrl;
        }

        $query = http_build_query($params);
        $separator = parse_url($baseUrl, PHP_URL_QUERY) ? '&' : '?';

        return $baseUrl . $separator . $query;
    }
}
