<?php

namespace SantosDave\Paystack\Resources;

class Refund extends BaseResource
{
    protected string $basePath = 'refund';

    /**
     * Create a refund.
     *
     * @param array $data Refund data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['transaction']);

        // Convert amount to subunits if provided
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'transaction',
            'amount',
            'currency',
            'customer_note',
            'merchant_note',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * List all refunds.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = [
            'perPage',
            'page',
            'from',
            'to',
            'reference',
            'currency',
            'transaction'
        ];

        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a refund.
     *
     * @param string $reference Refund reference
     * @return array
     */
    public function fetch(string $reference): array
    {
        return $this->client->get($this->buildPath($reference));
    }
}