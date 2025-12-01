<?php

namespace SantosDave\Paystack\Resources;

class Integration extends BaseResource
{
    protected string $basePath = 'integration';

    /**
     * Fetch payment session timeout.
     *
     * @return array
     */
    public function fetchTimeout(): array
    {
        return $this->client->get($this->buildPath('payment_session_timeout'));
    }

    /**
     * Update payment session timeout.
     *
     * @param int $timeout Timeout in seconds
     * @return array
     */
    public function updateTimeout(int $timeout): array
    {
        return $this->client->put($this->buildPath('payment_session_timeout'), [
            'timeout' => $timeout
        ]);
    }
}
