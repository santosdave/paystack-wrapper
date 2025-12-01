<?php

namespace SantosDave\Paystack\Resources;

class Settlement extends BaseResource
{
    protected string $basePath = 'settlement';

    /**
     * List all settlements.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to', 'subaccount'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch settlement transactions.
     *
     * @param string $id Settlement ID
     * @param array $params Query parameters
     * @return array
     */
    public function fetchTransactions(string $id, array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath("{$id}/transactions"), $query);
    }
}
