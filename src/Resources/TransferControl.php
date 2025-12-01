<?php

namespace SantosDave\Paystack\Resources;

class TransferControl extends BaseResource
{
    protected string $basePath = 'balance';

    /**
     * Check balance.
     *
     * @return array
     */
    public function checkBalance(): array
    {
        return $this->client->get($this->buildPath());
    }

    /**
     * Fetch balance ledger.
     *
     * @return array
     */
    public function fetchBalanceLedger(): array
    {
        return $this->client->get($this->buildPath('ledger'));
    }

    /**
     * Resend OTP.
     *
     * @param array $data OTP data
     * @return array
     */
    public function resendOtp(array $data): array
    {
        $this->validateRequired($data, ['transfer_code', 'reason']);

        $allowed = ['transfer_code', 'reason'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post('/transfer/resend_otp', $params);
    }

    /**
     * Disable OTP.
     *
     * @return array
     */
    public function disableOtp(): array
    {
        return $this->client->post('/transfer/disable_otp');
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

        return $this->client->post('/transfer/disable_otp_finalize', $data);
    }

    /**
     * Enable OTP.
     *
     * @return array
     */
    public function enableOtp(): array
    {
        return $this->client->post('/transfer/enable_otp');
    }
}
