<?php

namespace SantosDave\Paystack\Resources;

class Plan extends BaseResource
{
    protected string $basePath = 'plan';

    /**
     * Create a plan.
     *
     * @param array $data Plan data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['name', 'amount', 'interval']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'name',
            'amount',
            'interval',
            'description',
            'currency',
            'invoice_limit',
            'send_invoices',
            'send_sms'
        ];

        $params = $this->filterParams($data, $allowed);

        $response = $this->client->post($this->buildPath(), $params);

        $this->forget('plans');

        return $response;
    }

    /**
     * List all plans.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to', 'interval', 'amount'];
        $query = $this->filterParams($params, $allowed);

        $cacheKey = 'plans:' . md5(json_encode($query));

        return $this->remember($cacheKey, function () use ($query) {
            return $this->client->get($this->buildPath(), $query);
        });
    }

    /**
     * Fetch a plan.
     *
     * @param string $idOrCode Plan ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        $cacheKey = "plan:{$idOrCode}";

        return $this->remember($cacheKey, function () use ($idOrCode) {
            return $this->client->get($this->buildPath($idOrCode));
        });
    }

    /**
     * Update a plan.
     *
     * @param string $idOrCode Plan ID or code
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
            'name',
            'amount',
            'interval',
            'description',
            'currency',
            'invoice_limit',
            'send_invoices',
            'send_sms'
        ];

        $params = $this->filterParams($data, $allowed);

        $response = $this->client->put($this->buildPath($idOrCode), $params);

        $this->forget("plan:{$idOrCode}");
        $this->forget('plans');

        return $response;
    }
}