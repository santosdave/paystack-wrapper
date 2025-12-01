<?php

namespace SantosDave\Paystack\Exceptions;

use Exception;
use Throwable;

/**
 * Base Paystack exception class.
 * 
 * All Paystack-specific exceptions extend this base class,
 * making it easy to catch all package exceptions.
 */
class PaystackException extends Exception
{
    protected array $context = [];
    protected ?array $response = null;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context data.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set the API response that caused the exception.
     */
    public function setResponse(array $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Get the API response that caused the exception.
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /**
     * Check if this exception has a response.
     */
    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    /**
     * Get a user-friendly error message.
     */
    public function getUserMessage(): string
    {
        return $this->message ?: 'An error occurred while processing your payment.';
    }

    /**
     * Convert exception to array for logging.
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'code' => $this->code,
            'file' => $this->file,
            'line' => $this->line,
            'context' => $this->context,
            'response' => $this->response,
        ];
    }
}