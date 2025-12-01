<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;

/**
 * Authentication exception.
 * 
 * Thrown when API authentication fails due to invalid or missing API keys.
 * 
 * Common causes:
 * - Invalid secret key
 * - Expired API key
 * - Wrong environment (test key in production)
 * - Missing authorization header
 */
class AuthenticationException extends PaystackException
{
    public function __construct(
        string $message = 'Authentication failed. Please check your API keys.',
        int $code = 401,
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
        return 'Verify that your PAYSTACK_SECRET_KEY is correct in your .env file. '
            . 'Ensure you\'re using the right key for your environment (test vs live).';
    }

    /**
     * Check if this is a test/live key mismatch.
     */
    public function isKeyMismatch(): bool
    {
        return str_contains(strtolower($this->message), 'test')
            || str_contains(strtolower($this->message), 'live');
    }
}