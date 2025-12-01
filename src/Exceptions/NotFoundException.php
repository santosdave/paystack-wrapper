<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;

/**
 * Resource not found exception.
 * 
 * Thrown when a requested resource (transaction, customer, etc.) 
 * cannot be found in Paystack.
 */
class NotFoundException extends PaystackException
{
    protected ?string $resourceType = null;
    protected ?string $resourceId = null;

    public function __construct(
        string $message = 'Resource not found.',
        int $code = 404,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * Set the resource type that wasn't found.
     */
    public function setResourceType(string $type): self
    {
        $this->resourceType = $type;
        return $this;
    }

    /**
     * Get the resource type.
     */
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * Set the resource ID that wasn't found.
     */
    public function setResourceId(string $id): self
    {
        $this->resourceId = $id;
        return $this;
    }

    /**
     * Get the resource ID.
     */
    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    /**
     * Get suggestion for fixing this error.
     */
    public function getSuggestion(): string
    {
        $suggestion = 'Verify that the resource exists and you have access to it.';

        if ($this->resourceType && $this->resourceId) {
            $suggestion = "The {$this->resourceType} with ID '{$this->resourceId}' was not found. "
                . "Please check the ID and try again.";
        }

        return $suggestion;
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        if ($this->resourceType) {
            return ucfirst($this->resourceType) . ' not found. Please check and try again.';
        }

        return 'The requested resource was not found.';
    }
}