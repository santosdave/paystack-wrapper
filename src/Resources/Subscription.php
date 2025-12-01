<?php

namespace SantosDave\Paystack\Resources;

class Subscription extends BaseResource
{
    protected string $basePath = 'subscription';

    /**
     * Create a subscription.
     *
     * @param array $data Subscription data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['customer', 'plan']);

        $allowed = [
            'customer',
            'plan',
            'authorization',
            'start_date'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * List all subscriptions.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'customer', 'plan', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a subscription.
     *
     * @param string $idOrCode Subscription ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Enable a subscription.
     *
     * @param array $data Enable data
     * @return array
     */
    public function enable(array $data): array
    {
        $this->validateRequired($data, ['code', 'token']);

        $allowed = ['code', 'token'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('enable'), $params);
    }

    /**
     * Disable a subscription.
     *
     * @param array $data Disable data
     * @return array
     */
    public function disable(array $data): array
    {
        $this->validateRequired($data, ['code', 'token']);

        $allowed = ['code', 'token'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('disable'), $params);
    }

    /**
     * Generate an update subscription link.
     *
     * @param string $code Subscription code
     * @return array
     */
    public function generateUpdateLink(string $code): array
    {
        return $this->client->get($this->buildPath("{$code}/manage/link"));
    }

    /**
     * Send an update subscription link.
     *
     * @param string $code Subscription code
     * @return array
     */
    public function sendUpdateLink(string $code): array
    {
        return $this->client->post($this->buildPath("{$code}/manage/email"));
    }
}