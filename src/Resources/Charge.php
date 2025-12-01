<?php

namespace SantosDave\Paystack\Resources;

class Charge extends BaseResource
{
    protected string $basePath = 'charge';

    /**
     * Create a charge.
     *
     * @param array $data Charge data
     * @return array
     */
    public function create(array $data): array
    {
        $this->validateRequired($data, ['email', 'amount']);

        // Convert amount to subunits
        if (isset($data['amount'])) {
            $data['amount'] = $this->toSubunit($data['amount'], $data['currency'] ?? null);
        }

        $allowed = [
            'email',
            'amount',
            'bank',
            'bank_transfer',
            'authorization_code',
            'pin',
            'metadata',
            'reference',
            'ussd',
            'mobile_money',
            'device_id',
            'currency'
        ];

        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath(), $params);
    }

    /**
     * Submit PIN.
     *
     * @param array $data PIN data
     * @return array
     */
    public function submitPin(array $data): array
    {
        $this->validateRequired($data, ['pin', 'reference']);

        $allowed = ['pin', 'reference'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('submit_pin'), $params);
    }

    /**
     * Submit OTP.
     *
     * @param array $data OTP data
     * @return array
     */
    public function submitOtp(array $data): array
    {
        $this->validateRequired($data, ['otp', 'reference']);

        $allowed = ['otp', 'reference'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('submit_otp'), $params);
    }

    /**
     * Submit phone.
     *
     * @param array $data Phone data
     * @return array
     */
    public function submitPhone(array $data): array
    {
        $this->validateRequired($data, ['phone', 'reference']);

        $allowed = ['phone', 'reference'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('submit_phone'), $params);
    }

    /**
     * Submit birthday.
     *
     * @param array $data Birthday data
     * @return array
     */
    public function submitBirthday(array $data): array
    {
        $this->validateRequired($data, ['birthday', 'reference']);

        $allowed = ['birthday', 'reference'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('submit_birthday'), $params);
    }

    /**
     * Submit address.
     *
     * @param array $data Address data
     * @return array
     */
    public function submitAddress(array $data): array
    {
        $this->validateRequired($data, ['address', 'reference', 'city', 'state', 'zipcode']);

        $allowed = ['address', 'reference', 'city', 'state', 'zipcode'];
        $params = $this->filterParams($data, $allowed);

        return $this->client->post($this->buildPath('submit_address'), $params);
    }

    /**
     * Check pending charge.
     *
     * @param string $reference Charge reference
     * @return array
     */
    public function checkPending(string $reference): array
    {
        return $this->client->get($this->buildPath($reference));
    }
}
