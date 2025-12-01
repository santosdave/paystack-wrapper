<?php

namespace SantosDave\Paystack\Resources;

class Transaction extends BaseResource
{
    protected string $basePath = 'transaction';

    /**
     * Initialize a transaction.
     *
     * @param array $data Transaction data
     * @return array
     */
    public function initialize(array $data): array
    {
        $this->validateRequired($data, ['email', 'amount']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        // Filter allowed parameters
        $allowed = [
            'email',
            'amount',
            'currency',
            'reference',
            'callback_url',
            'plan',
            'invoice_limit',
            'metadata',
            'channels',
            'split_code',
            'subaccount',
            'transaction_charge',
            'bearer'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('initialize'), $params);
    }

    /**
     * Verify a transaction.
     *
     * @param string $reference Transaction reference
     * @return array
     */
    public function verify(string $reference): array
    {
        return $this->client->get($this->buildPath("verify/{$reference}"));
    }

    /**
     * List all transactions.
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
            'customer',
            'status',
            'currency',
            'amount',
            'settled',
            'settlement',
            'payment_page'
        ];

        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a single transaction.
     *
     * @param int $id Transaction ID
     * @return array
     */
    public function fetch(int $id): array
    {
        return $this->client->get($this->buildPath((string)$id));
    }

    /**
     * Charge an authorization.
     *
     * @param array $data Charge data
     * @return array
     */
    public function charge(array $data): array
    {
        $this->validateRequired($data, ['email', 'amount', 'authorization_code']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'email',
            'amount',
            'authorization_code',
            'reference',
            'currency',
            'metadata',
            'channels',
            'subaccount',
            'transaction_charge',
            'bearer',
            'queue'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('charge_authorization'), $params);
    }

    /**
     * Get transaction timeline.
     *
     * @param string $idOrReference Transaction ID or reference
     * @return array
     */
    public function timeline(string $idOrReference): array
    {
        return $this->client->get($this->buildPath("timeline/{$idOrReference}"));
    }

    /**
     * Get transaction totals.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function totals(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath('totals'), $query);
    }

    /**
     * Export transactions.
     *
     * @param array $params Export parameters
     * @return array
     */
    public function export(array $params = []): array
    {
        $allowed = [
            'perPage',
            'page',
            'from',
            'to',
            'customer',
            'status',
            'currency',
            'amount',
            'settled',
            'settlement',
            'payment_page'
        ];

        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath('export'), $query);
    }

    /**
     * Partial debit a transaction.
     *
     * @param array $data Debit data
     * @return array
     */
    public function partialDebit(array $data): array
    {
        $this->validateRequired($data, ['authorization_code', 'currency', 'amount', 'email']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'authorization_code',
            'currency',
            'amount',
            'email',
            'reference',
            'at_least',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('partial_debit'), $params);
    }

    /**
     * Check if authorization is reusable.
     *
     * @param string $authorizationCode Authorization code
     * @return array
     */
    public function checkAuthorization(string $authorizationCode): array
    {
        return $this->client->get($this->buildPath("check_authorization/{$authorizationCode}"));
    }
}