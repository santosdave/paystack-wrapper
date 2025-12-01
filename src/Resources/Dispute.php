<?php

namespace SantosDave\Paystack\Resources;

class Dispute extends BaseResource
{
    protected string $basePath = 'dispute';

    /**
     * List all disputes.
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
            'transaction',
            'status'
        ];

        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a dispute.
     *
     * @param string $id Dispute ID
     * @return array
     */
    public function fetch(string $id): array
    {
        return $this->client->get($this->buildPath($id));
    }

    /**
     * List disputes for a transaction.
     *
     * @param string $id Transaction ID
     * @return array
     */
    public function listTransactionDisputes(string $id): array
    {
        return $this->client->get($this->buildPath("transaction/{$id}"));
    }

    /**
     * Update a dispute.
     *
     * @param string $id Dispute ID
     * @param array $data Update data
     * @return array
     */
    public function update(string $id, array $data): array
    {
        $this->validateRequired($data, ['refund_amount']);

        // Convert amount to subunits
        if (isset($data['refund_amount'])) {
            $data['refund_amount'] = $this->toSubunit(
                $data['refund_amount'],
                $data['currency'] ?? null
            );
        }

        $allowed = ['refund_amount', 'uploaded_filename'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath($id), $params);
    }

    /**
     * Add evidence to a dispute.
     *
     * @param string $id Dispute ID
     * @param array $data Evidence data
     * @return array
     */
    public function addEvidence(string $id, array $data): array
    {
        $this->validateRequired($data, [
            'customer_email',
            'customer_name',
            'customer_phone',
            'service_details'
        ]);

        $allowed = [
            'customer_email',
            'customer_name',
            'customer_phone',
            'service_details',
            'delivery_address',
            'delivery_date'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath("{$id}/evidence"), $params);
    }

    /**
     * Get upload URL for dispute evidence.
     *
     * @param string $id Dispute ID
     * @param array $data Upload data
     * @return array
     */
    public function getUploadUrl(string $id, array $data = []): array
    {
        $allowed = ['upload_filename'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->get($this->buildPath("{$id}/upload_url"), $params);
    }

    /**
     * Resolve a dispute.
     *
     * @param string $id Dispute ID
     * @param array $data Resolution data
     * @return array
     */
    public function resolve(string $id, array $data): array
    {
        $this->validateRequired($data, ['resolution', 'message', 'uploaded_filename']);

        // Convert refund amount to subunits if provided
        if (isset($data['refund_amount'])) {
            $data['refund_amount'] = $this->toSubunit(
                $data['refund_amount'],
                $data['currency'] ?? null
            );
        }

        $allowed = [
            'resolution',
            'message',
            'refund_amount',
            'uploaded_filename',
            'evidence'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath("{$id}/resolve"), $params);
    }

    /**
     * Export disputes.
     *
     * @param array $params Export parameters
     * @return array
     */
    public function export(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to', 'transaction', 'status'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath('export'), $query);
    }
}
