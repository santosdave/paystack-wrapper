<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;

/**
 * Invalid request exception.
 * 
 * Thrown when the API request is malformed or contains invalid data
 * that doesn't match expected format.
 */
class InvalidRequestException extends PaystackException
{
    public function __construct(
        string $message = 'Invalid request.',
        int $code = 400,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * Get suggestion for fixing this error.
     */
    public function getSuggestion(): string
    {
        return 'Check that all required parameters are provided and in the correct format. '
            . 'Review the API documentation for the correct request structure.';
    }

    /**
     * Check if this is a parameter type error.
     */
    public function isTypeError(): bool
    {
        return str_contains(strtolower($this->message), 'type')
            || str_contains(strtolower($this->message), 'format');
    }

    /**
     * Check if this is a missing parameter error.
     */
    public function isMissingParameterError(): bool
    {
        return str_contains(strtolower($this->message), 'required')
            || str_contains(strtolower($this->message), 'missing');
    }
}