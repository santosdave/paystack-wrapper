<?php

namespace SantosDave\Paystack\Resources;

use SantosDave\Paystack\Http\Client;
use Illuminate\Support\Facades\Cache;

abstract class BaseResource
{
    protected Client $client;
    protected string $basePath = '';
    protected array $config;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->config = config('paystack', []);
    }

    /**
     * Build the full endpoint path.
     */
    protected function buildPath(string $path = ''): string
    {
        $fullPath = $this->basePath;

        if ($path) {
            $fullPath .= '/' . ltrim($path, '/');
        }

        return '/' . trim($fullPath, '/');
    }

    /**
     * Convert amount to subunits (kobo, cents, etc.).
     */
    protected function toSubunit(float $amount, ?string $currency = null): int
    {
        $currency = $currency ?? $this->config['currency'] ?? 'NGN';

        // XOF doesn't have subunits, but we still multiply by 100
        // and ignore fractional parts as per Paystack documentation
        return (int) ($amount * 100);
    }

    /**
     * Convert amount from subunits to main unit.
     */
    protected function fromSubunit(int $amount, ?string $currency = null): float
    {
        return $amount / 100;
    }

    /**
     * Validate required parameters.
     */
    protected function validateRequired(array $data, array $required): void
    {
        $missing = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Missing required parameters: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Filter only allowed parameters.
     */
    protected function filterParams(array $data, array $allowed): array
    {
        return array_intersect_key($data, array_flip($allowed));
    }

    /**
     * Get from cache or execute callback.
     */
    protected function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (!($this->config['cache']['enabled'] ?? true)) {
            return $callback();
        }

        $prefix = $this->config['cache']['prefix'] ?? 'paystack';
        $ttl = $ttl ?? ($this->config['cache']['ttl'] ?? 3600);
        $cacheKey = "{$prefix}:{$key}";

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Forget cached data.
     */
    protected function forget(string $key): void
    {
        if ($this->config['cache']['enabled'] ?? true) {
            $prefix = $this->config['cache']['prefix'] ?? 'paystack';
            $cacheKey = "{$prefix}:{$key}";
            Cache::forget($cacheKey);
        }
    }

    /**
     * Build query parameters for pagination.
     */
    protected function buildPaginationParams(
        ?int $page = null,
        ?int $perPage = null,
        ?string $from = null,
        ?string $to = null
    ): array {
        $params = [];

        if ($page !== null) {
            $params['page'] = $page;
        }

        if ($perPage !== null) {
            $params['perPage'] = $perPage;
        }

        if ($from !== null) {
            $params['from'] = $from;
        }

        if ($to !== null) {
            $params['to'] = $to;
        }

        return $params;
    }

    /**
     * Clean array by removing null values.
     */
    protected function cleanArray(array $data): array
    {
        return array_filter($data, fn($value) => $value !== null);
    }
}