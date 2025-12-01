<?php

namespace SantosDave\Paystack;

use SantosDave\Paystack\Http\Client;
use SantosDave\Paystack\Resources\Transaction;
use SantosDave\Paystack\Resources\Customer;
use SantosDave\Paystack\Resources\Plan;
use SantosDave\Paystack\Resources\Subscription;
use SantosDave\Paystack\Resources\Subaccount;
use SantosDave\Paystack\Resources\TransferRecipient;
use SantosDave\Paystack\Resources\Transfer;
use SantosDave\Paystack\Resources\Refund;
use SantosDave\Paystack\Resources\Dispute;
use SantosDave\Paystack\Resources\Verification;
use SantosDave\Paystack\Resources\Miscellaneous;
use SantosDave\Paystack\Resources\PaymentPage;
use SantosDave\Paystack\Resources\PaymentRequest;
use SantosDave\Paystack\Resources\Settlement;
use SantosDave\Paystack\Resources\TransferControl;
use SantosDave\Paystack\Resources\BulkCharge;
use SantosDave\Paystack\Resources\Integration;
use SantosDave\Paystack\Resources\Charge;
use SantosDave\Paystack\Resources\DedicatedAccount;

class Paystack
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Access Transaction resource.
     */
    public function transaction(): Transaction
    {
        return new Transaction($this->client);
    }

    /**
     * Access Customer resource.
     */
    public function customer(): Customer
    {
        return new Customer($this->client);
    }

    /**
     * Access Plan resource.
     */
    public function plan(): Plan
    {
        return new Plan($this->client);
    }

    /**
     * Access Subscription resource.
     */
    public function subscription(): Subscription
    {
        return new Subscription($this->client);
    }

    /**
     * Access Subaccount resource.
     */
    public function subaccount(): Subaccount
    {
        return new Subaccount($this->client);
    }

    /**
     * Access Transfer Recipient resource.
     */
    public function transferRecipient(): TransferRecipient
    {
        return new TransferRecipient($this->client);
    }

    /**
     * Access Transfer resource.
     */
    public function transfer(): Transfer
    {
        return new Transfer($this->client);
    }

    /**
     * Access Refund resource.
     */
    public function refund(): Refund
    {
        return new Refund($this->client);
    }

    /**
     * Access Dispute resource.
     */
    public function dispute(): Dispute
    {
        return new Dispute($this->client);
    }

    /**
     * Access Verification resource.
     */
    public function verification(): Verification
    {
        return new Verification($this->client);
    }

    /**
     * Access Miscellaneous resource.
     */
    public function miscellaneous(): Miscellaneous
    {
        return new Miscellaneous($this->client);
    }

    /**
     * Access Payment Page resource.
     */
    public function paymentPage(): PaymentPage
    {
        return new PaymentPage($this->client);
    }

    /**
     * Access Payment Request resource.
     */
    public function paymentRequest(): PaymentRequest
    {
        return new PaymentRequest($this->client);
    }

    /**
     * Access Settlement resource.
     */
    public function settlement(): Settlement
    {
        return new Settlement($this->client);
    }

    /**
     * Access Transfer Control resource.
     */
    public function transferControl(): TransferControl
    {
        return new TransferControl($this->client);
    }

    /**
     * Access Bulk Charge resource.
     */
    public function bulkCharge(): BulkCharge
    {
        return new BulkCharge($this->client);
    }

    /**
     * Access Integration resource.
     */
    public function integration(): Integration
    {
        return new Integration($this->client);
    }

    /**
     * Access Charge resource.
     */
    public function charge(): Charge
    {
        return new Charge($this->client);
    }

    /**
     * Access Dedicated Account resource.
     */
    public function dedicatedAccount(): DedicatedAccount
    {
        return new DedicatedAccount($this->client);
    }

    /**
     * Get the HTTP client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}