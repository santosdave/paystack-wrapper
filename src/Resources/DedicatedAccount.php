<?php

namespace SantosDave\Paystack\Resources;

class DedicatedAccount extends BaseResource
{
    protected string $basePath = 'dedicated_account';

    /**
     * Create a dedicated virtual account.
     *
     * @param array $data Account data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['customer']);

        $allowed = [
            'customer',
            'preferred_bank',
            'subaccount',
            'split_code',
            'first_name',
            'last_name',
            'phone'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * Assign a dedicated virtual account.
     *
     * @param array $data Assignment data
     * @return array
     */
    public function assign(array $data): array
    {
        $this->validateRequired($data, ['email', 'first_name', 'last_name', 'phone', 'preferred_bank', 'country']);

        $allowed = [
            'email',
            'first_name',
            'last_name',
            'phone',
            'preferred_bank',
            'country',
            'account_number',
            'bvn',
            'bank_code',
            'subaccount',
            'split_code'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('assign'), $params);
    }

    /**
     * List dedicated accounts.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['active', 'currency', 'provider_slug', 'bank_id', 'customer', 'perPage', 'page'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a dedicated account.
     *
     * @param int $id Account ID
     * @return array
     */
    public function fetch(int $id): array
    {
        return $this->client->get($this->buildPath((string)$id));
    }

    /**
     * Requery dedicated account.
     *
     * @param array $data Requery data
     * @return array
     */
    public function requery(array $data): array
    {
        $allowed = ['account_number', 'provider_slug', 'date'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->get($this->buildPath('requery'), $params);
    }

    /**
     * Deactivate a dedicated account.
     *
     * @param int $id Account ID
     * @return array
     */
    public function deactivate(int $id): array
    {
        return $this->client->delete($this->buildPath((string)$id));
    }

    /**
     * Split a dedicated account transaction.
     *
     * @param array $data Split data
     * @return array
     */
    public function split(array $data): array
    {
        $this->validateRequired($data, ['customer']);

        $allowed = ['customer', 'subaccount', 'split_code', 'preferred_bank'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('split'), $params);
    }

    /**
     * Remove split from dedicated account.
     *
     * @param array $data Account data
     * @return array
     */
    public function removeSplit(array $data): array
    {
        $this->validateRequired($data, ['account_number']);

        return $this->client->delete($this->buildPath('split'), $data);
    }

    /**
     * Fetch available providers.
     *
     * @return array
     */
    public function fetchProviders(): array
    {
        $cacheKey = 'dedicated_account_providers';

        return $this->remember($cacheKey, function () {
            return $this->client->get($this->buildPath('available_providers'));
        }, 86400); // Cache for 24 hours
    }
}
