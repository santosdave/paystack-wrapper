<?php

namespace SantosDave\Paystack\Resources;

class Transfer extends BaseResource
{
    protected string $basePath = 'transfer';

    /**
     * Initiate a transfer.
     *
     * @param array $data Transfer data
     * @return array
     */
    public function initiate(array $data): array
    {
        $this->validateRequired($data, ['source', 'amount', 'recipient']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'source',
            'amount',
            'recipient',
            'reason',
            'currency',
            'reference',
            'metadata'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * Initiate bulk transfers.
     *
     * @param array $data Bulk transfer data
     * @return array
     */
    public function bulkInitiate(array $data): array
    {
        $this->validateRequired($data, ['source', 'transfers']);

        // Convert amounts to subunits for each transfer
        if (isset($data['transfers']) && is_array($data['transfers'])) {
            foreach ($data['transfers'] as $key => $transfer) {
                if (isset($transfer['amount'])) {
                    $data['transfers'][$key]['amount'] = $this->toSubunit(
                        $transfer['amount'],
                        $transfer['currency'] ?? null
                    );
                }
            }
        }

        return $this->client->post($this->buildPath('bulk'), $data);
    }

    /**
     * List all transfers.
     *
     * @param array $params Query parameters
     * @return array
     */
    public function list(array $params = []): array
    {
        $allowed = ['perPage', 'page', 'from', 'to', 'customer'];
        $query = $this->filterParams($params, $allowed);

        return $this->client->get($this->buildPath(), $query);
    }

    /**
     * Fetch a transfer.
     *
     * @param string $idOrCode Transfer ID or code
     * @return array
     */
    public function fetch(string $idOrCode): array
    {
        return $this->client->get($this->buildPath($idOrCode));
    }

    /**
     * Finalize a transfer.
     *
     * @param array $data Finalize data
     * @return array
     */
    public function finalize(array $data): array
    {
        $this->validateRequired($data, ['transfer_code', 'otp']);

        $allowed = ['transfer_code', 'otp'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('finalize_transfer'), $params);
    }

    /**
     * Verify a transfer.
     *
     * @param string $reference Transfer reference
     * @return array
     */
    public function verify(string $reference): array
    {
        return $this->client->get($this->buildPath("verify/{$reference}"));
    }

    /**
     * Resend OTP for transfer.
     *
     * @param array $data Transfer data
     * @return array
     */
    public function resendOtp(array $data): array
    {
        $this->validateRequired($data, ['transfer_code', 'reason']);

        $allowed = ['transfer_code', 'reason'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('resend_otp'), $params);
    }

    /**
     * Disable OTP requirement for transfers.
     *
     * @return array
     */
    public function disableOtp(): array
    {
        return $this->client->post($this->buildPath('disable_otp'));
    }

    /**
     * Finalize disable OTP.
     *
     * @param array $data OTP data
     * @return array
     */
    public function finalizeDisableOtp(array $data): array
    {
        $this->validateRequired($data, ['otp']);

        return $this->client->post($this->buildPath('disable_otp_finalize'), $data);
    }

    /**
     * Enable OTP requirement for transfers.
     *
     * @return array
     */
    public function enableOtp(): array
    {
        return $this->client->post($this->buildPath('enable_otp'));
    }
}