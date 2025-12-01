<?php

namespace SantosDave\Paystack\Resources;

class PaymentRequest extends BaseResource
{
    protected string $basePath = 'paymentrequest';

    /**
     * Create a payment request.
     *
     * @param array $data Request data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['customer', 'amount']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'customer',
            'amount',
            'due_date',
            'description',
            'line_items',
            'tax',
            'currency',
            'send_notification',
            'draft',
            'has_invoice',
            'invoice_number',
            'split_code'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * List all payment requests.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'customer', 'status', 'currency', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a payment request.
     *
     * @param string $idOrCode Request ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Verify a payment request.
     *
     * @param string $code Request code
     * @return array
     */
    public function verify(string $code): array
    {
        return $this->client->get($this->buildPath("verify/{$code}"));
    }

    /**
     * Send notification for a payment request.
     *
     * @param string $code Request code
     * @return array
     */
    public function sendNotification(string $code): array
    {
        return $this->client->post($this->buildPath("notify/{$code}"));
    }

    /**
     * Get payment request total.
     *
     * @return array
     */
    public function total(): array
    {
        return $this->client->get($this->buildPath('totals'));
    }

    /**
     * Finalize a draft payment request.
     *
     * @param string $code Request code
     * @return array
     */
    public function finalize(string $code): array
    {
        return $this->client->post($this->buildPath("finalize/{$code}"));
    }

    /**
     * Update a payment request.
     *
     * @param string $idOrCode Request ID or code
     * @param array $data Update data
     * @return array
     */
    public function update(string $idOrCode, array $data): array
    {
        // Convert amount to subunits if provided
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'customer',
            'amount',
            'due_date',
            'description',
            'line_items',
            'tax',
            'currency',
            'send_notification',
            'draft',
            'has_invoice',
            'invoice_number',
            'split_code'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath($idOrCode), $params);
    }

    /**
     * Archive a payment request.
     *
     * @param string $code Request code
     * @return array
     */
    public function archive(string $code): array
    {
        return $this->client->post($this->buildPath("archive/{$code}"));
    }
}
