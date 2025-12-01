<?php

namespace SantosDave\Paystack\Resources;

class Subaccount extends BaseResource
{
    protected string $basePath = 'subaccount';

    /**
     * Create a subaccount.
     *
     * @param array $data Subaccount data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, [
            'business_name',
            'settlement_bank',
            'account_number',
            'percentage_charge'
        ]);

        $allowed = [
            'business_name',
            'settlement_bank',
            'account_number',
            'percentage_charge',
            'description',
            'primary_contact_email',
            'primary_contact_name',
            'primary_contact_phone',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * List all subaccounts.
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
     * Fetch a subaccount.
     *
     * @param string $idOrCode Subaccount ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Update a subaccount.
     *
     * @param string $idOrCode Subaccount ID or code
     * @param array $data Update data
     * @return array
     */
    public function update(string $idOrCode, array $data): array
    {
        $allowed = [
            'business_name',
            'settlement_bank',
            'account_number',
            'active',
            'percentage_charge',
            'description',
            'primary_contact_email',
            'primary_contact_name',
            'primary_contact_phone',
            'settlement_schedule',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->put($this->buildPath($idOrCode), $params);
    }
}