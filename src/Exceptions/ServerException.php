<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;


/**
 * Server exception.
 * 
 * Thrown when Paystack API returns 5xx server errors.
 */
class ServerException extends PaystackException
{
    public function __construct(
        string $message = 'Server error occurred.',
        int $code = 500,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * Check if this is a maintenance error.
     */
    public function isMaintenance(): bool
    {
        return $this->code === 503
            || str_contains(strtolower($this->message), 'maintenance');
    }

    /**
     * Get suggestion for fixing this error.
     */
    public function getSuggestion(): string
    {
        if ($this->isMaintenance()) {
            return 'Paystack API is currently under maintenance. Please try again later.';
        }

        return 'Paystack API encountered an error. This is temporary - please try again in a few moments. '
            . 'If the problem persists, contact Paystack support.';
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        if ($this->isMaintenance()) {
            return 'Payment system is temporarily unavailable. Please try again shortly.';
        }

        return 'A temporary error occurred. Please try again in a moment.';
    }
}