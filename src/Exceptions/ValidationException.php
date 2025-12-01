<?php

namespace SantosDave\Paystack\Exceptions;

use Exception;
use Throwable;

/**
 * Validation exception.
 */
/**
 * Validation exception.
 * 
 * Thrown when request parameters fail validation.
 * Contains detailed error information for each field.
 */
class ValidationException extends PaystackException
{
    protected array $errors = [];

    public function __construct(
        string $message = 'Validation failed.',
        int $code = 422,
        array $errors = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if a specific field has errors.
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get error for a specific field.
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get all error messages as a flat array.
     */
    public function getErrorMessages(): array
    {
        $messages = [];

        foreach ($this->errors as $field => $error) {
            if (is_array($error)) {
                $messages = array_merge($messages, $error);
            } else {
                $messages[] = $error;
            }
        }

        return $messages;
    }

    /**
     * Get first error message.
     */
    public function getFirstError(): ?string
    {
        $messages = $this->getErrorMessages();
        return $messages[0] ?? null;
    }

    /**
     * Convert errors to string for display.
     */
    public function getErrorsAsString(string $separator = ', '): string
    {
        return implode($separator, $this->getErrorMessages());
    }

    /**
     * Get user-friendly validation message.
     */
    public function getUserMessage(): string
    {
        $firstError = $this->getFirstError();
        return $firstError ?: 'The provided data is invalid.';
    }
}