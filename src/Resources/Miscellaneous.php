<?php

namespace SantosDave\Paystack\Resources;

class Miscellaneous extends BaseResource
{
    /**
     * List all supported banks.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function listBanks(array $params = []): array
    {
        $allowed = ['country', 'use_cursor', 'perPage', 'next', 'previous', 'gateway', 'type', 'currency'];
        $query = $this->filterParams($params, $allowed);

        $cacheKey = 'banks:' . md5(json_encode($query));

        return $this->remember($cacheKey, function () use ($query) {
            return $this->client->get('/bank', $query);
        }, 86400); // Cache for 24 hours
    }

    /**
     * List all countries.
     *
     * @return array
     */
    public function listCountries(): array
    {
        return $this->remember('countries', function () {
            return $this->client->get('/country');
        }, 86400); // Cache for 24 hours
    }

    /**
     * List all supported states.
     *
     * @param string $country Country code
     * @return array
     */
    public function listStates(string $country): array
    {
        $cacheKey = "states:{$country}";

        return $this->remember($cacheKey, function () use ($country) {
            return $this->client->get('/address_verification/states', [
                'country' => $country
            ]);
        }, 86400);
    }
}
