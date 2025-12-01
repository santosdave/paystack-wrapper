<?php

namespace SantosDave\Paystack\Resources;

class TransferRecipient extends BaseResource
{
    protected string $basePath = 'transferrecipient';

    /**
     * Create a transfer recipient.
     *
     * @param array $data Recipient data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['type', 'name', 'account_number', 'bank_code']);

        $allowed = [
            'type',
            'name',
            'account_number',
            'bank_code',
            'description',
            'currency',
            'authorization_code',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * Create multiple transfer recipients.
     *
     * @param array $data Bulk recipient data
     * @return array
     */
    public function bulkCreate(array $data): array
    {
        $this->validateRequired($data, ['batch']);

        return $this->client->post($this->buildPath('bulk'), $data);
    }

    /**
     * List all transfer recipients.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a transfer recipient.
     *
     * @param string $idOrCode Recipient ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Update a transfer recipient.
     *
     * @param string $idOrCode Recipient ID or code
     * @param array $data Update data
     * @return array
     */
    public function update(string $idOrCode, array $data): array
    {
        $allowed = ['name', 'email', 'metadata'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath($idOrCode), $params);
    }

    /**
     * Delete a transfer recipient.
     *
     * @param string $idOrCode Recipient ID or code
     * @return array
     */
    public function delete(string $idOrCode): array
    {
        return $this->client->delete($this->buildPath($idOrCode));
    }
}