<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;

/**
 * Network exception.
 * 
 * Thrown when network-level errors occur (timeouts, connection failures, etc.).
 */
class NetworkException extends PaystackException
{
    public function __construct(
        string $message = 'Network error occurred.',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * Check if this is a timeout error.
     */
    public function isTimeout(): bool
    {
        return str_contains(strtolower($this->message), 'timeout')
            || str_contains(strtolower($this->message), 'timed out');
    }

    /**
     * Check if this is a connection error.
     */
    public function isConnectionError(): bool
    {
        return str_contains(strtolower($this->message), 'connection')
            || str_contains(strtolower($this->message), 'could not connect');
    }

    /**
     * Check if this is an SSL error.
     */
    public function isSslError(): bool
    {
        return str_contains(strtolower($this->message), 'ssl')
            || str_contains(strtolower($this->message), 'certificate');
    }

    /**
     * Get suggestion for fixing this error.
     */
    public function getSuggestion(): string
    {
        if ($this->isTimeout()) {
            return 'The request timed out. Check your internet connection or increase the timeout value in config.';
        }

        if ($this->isConnectionError()) {
            return 'Could not connect to Paystack API. Check your internet connection and firewall settings.';
        }

        if ($this->isSslError()) {
            return 'SSL certificate verification failed. Ensure SSL verification is enabled and certificates are up to date.';
        }

        return 'A network error occurred. Please check your internet connection and try again.';
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        return 'Connection issue. Please check your internet and try again.';
    }
}