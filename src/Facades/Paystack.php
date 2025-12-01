<?php

namespace SantosDave\Paystack\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \SantosDave\Paystack\Resources\Transaction transaction()
 * @method static \SantosDave\Paystack\Resources\Customer customer()
 * @method static \SantosDave\Paystack\Resources\Plan plan()
 * @method static \SantosDave\Paystack\Resources\Subscription subscription()
 * @method static \SantosDave\Paystack\Resources\Subaccount subaccount()
 * @method static \SantosDave\Paystack\Resources\TransferRecipient transferRecipient()
 * @method static \SantosDave\Paystack\Resources\Transfer transfer()
 * @method static \SantosDave\Paystack\Resources\Refund refund()
 * @method static \SantosDave\Paystack\Resources\Dispute dispute()
 * @method static \SantosDave\Paystack\Resources\Verification verification()
 * @method static \SantosDave\Paystack\Resources\Miscellaneous miscellaneous()
 * 
 * @see \SantosDave\Paystack\Paystack
 */
class Paystack extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'paystack';
    }
}