<?php

namespace SantosDave\Paystack\Resources;

class Verification extends BaseResource
{
    protected string $basePath = 'bank';

    /**
     * Resolve account number.
     *
     * @param array $data Account data
     * @return array
     */
    public function resolveAccountNumber(array $data): array
    {
        $this->validateRequired($data, ['account_number', 'bank_code']);

        $allowed = ['account_number', 'bank_code'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->get($this->buildPath('resolve'), $params);
    }

    /**
     * Validate account.
     *
     * @param array $data Account data
     * @return array
     */
    public function validateAccount(array $data): array
    {
        $this->validateRequired($data, [
            'account_name',
            'account_number',
            'account_type',
            'bank_code',
            'country_code',
            'document_type'
        ]);

        $allowed = [
            'account_name',
            'account_number',
            'account_type',
            'bank_code',
            'country_code',
            'document_type',
            'document_number'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('validate'), $params);
    }

    /**
     * Resolve BIN (Bank Identification Number).
     *
     * @param string $bin BIN number
     * @return array
     */
    public function resolveBin(string $bin): array
    {
        return $this->client->get('/decision/bin/' . $bin);
    }

    /**
     * Resolve card BIN.
     *
     * @param string $bin Card BIN
     * @return array
     */
    public function resolveCardBin(string $bin): array
    {
        $cacheKey = "card_bin:{$bin}";

        return $this->remember($cacheKey, function () use ($bin) {
            return $this->client->get('/decision/bin/' . $bin);
        }, 86400); // Cache for 24 hours
    }

    /**
     * Match BVN (Bank Verification Number).
     *
     * @param array $data BVN data
     * @return array
     */
    public function matchBvn(array $data): array
    {
        $this->validateRequired($data, ['bvn', 'account_number', 'bank_code']);

        $allowed = [
            'bvn',
            'account_number',
            'bank_code',
            'first_name',
            'last_name'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post('/bvn/match', $params);
    }

    /**
     * Resolve BVN.
     *
     * @param string $bvn BVN number
     * @return array
     */
    public function resolveBvn(string $bvn): array
    {
        return $this->client->get("/bvn/resolve/{$bvn}");
    }

    /**
     * Get address verification states.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function getAddressStates(array $params = []): array
    {
        $allowed = ['type', 'country', 'currency'];
        $query = $this->filterParams($params, $allowed);

        $cacheKey = 'address_states:' . md5(json_encode($query));

        return $this->remember($cacheKey, function () use ($query) {
            return $this->client->get('/address_verification/states', $query);
        }, 3600);
    }
}
