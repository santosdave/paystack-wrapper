<?php

namespace SantosDave\Paystack\Resources;

class Customer extends BaseResource
{
    protected string $basePath = 'customer';

    /**
     * Create a customer.
     *
     * @param array $data Customer data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['email']);

        $allowed = [
            'email',
            'first_name',
            'last_name',
            'phone',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        $response = $this->client->post($this->buildPath(), $params);

        // Clear cache for customer list
        $this->forget('customers');

        return $response;
    }

    /**
     * List all customers.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        $cacheKey = 'customers:' . md5(json_encode($query));

        return $this->remember($cacheKey, function () use ($query) {
            return $this->client->get($this->buildPath(), $query);
        });
    }

    /**
     * Fetch a customer.
     *
     * @param string $emailOrCode Customer email or code
     * @return array
     */
    public function fetch(string $emailOrCode): array
    {
        $cacheKey = "customer:{$emailOrCode}";

        return $this->remember($cacheKey, function () use ($emailOrCode) {
            return $this->client->get($this->buildPath($emailOrCode));
        });
    }

    /**
     * Update a customer.
     *
     * @param string $code Customer code
     * @param array $data Update data
     * @return array
     */
    public function update(string $code, array $data): array
    {
        $allowed = ['first_name', 'last_name', 'phone', 'metadata'];
        $params = $this->filterParams($data, $allowed);

        $response = $this->client->put($this->buildPath($code), $params);

        // Clear customer cache
        $this->forget("customer:{$code}");
        $this->forget('customers');

        return $response;
    }

    /**
     * Validate a customer.
     *
     * @param string $code Customer code
     * @param array $data Validation data
     * @return array
     */
    public function validate(string $code, array $data): array
    {
        $this->validateRequired($data, ['first_name', 'last_name', 'type', 'value', 'country']);

        $allowed = [
            'first_name',
            'last_name',
            'type',
            'value',
            'country',
            'bvn',
            'bank_code',
            'account_number',
            'middle_name'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath("{$code}/identification"), $params);
    }

    /**
     * Whitelist or blacklist a customer.
     *
     * @param array $data Risk action data
     * @return array
     */
    public function setRiskAction(array $data): array
    {
        $this->validateRequired($data, ['customer', 'risk_action']);

        $allowed = ['customer', 'risk_action'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('set_risk_action'), $params);
    }

    /**
     * Deactivate an authorization.
     *
     * @param string $authorizationCode Authorization code
     * @return array
     */
    public function deactivateAuthorization(string $authorizationCode): array
    {
        return $this->client->post($this->buildPath('deactivate_authorization'), [
            'authorization_code' => $authorizationCode,
        ]);
    }
}