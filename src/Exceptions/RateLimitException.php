<?php

namespace SantosDave\Paystack\Exceptions;

use Throwable;

/**
 * Rate limit exception.
 * 
 * Thrown when API rate limits are exceeded.
 * Contains information about when to retry.
 */
class RateLimitException extends PaystackException
{
    protected ?int $retryAfter = null;
    protected ?int $limit = null;
    protected ?int $remaining = null;

    public function __construct(
        string $message = 'Rate limit exceeded. Please try again later.',
        int $code = 429,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous, $context);
    }

    /**
     * Set retry-after seconds.
     */
    public function setRetryAfter(int $seconds): self
    {
        $this->retryAfter = $seconds;
        return $this;
    }

    /**
     * Get retry-after seconds.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Set rate limit.
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Get rate limit.
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Set remaining requests.
     */
    public function setRemaining(int $remaining): self
    {
        $this->remaining = $remaining;
        return $this;
    }

    /**
     * Get remaining requests.
     */
    public function getRemaining(): ?int
    {
        return $this->remaining;
    }

    /**
     * Check if we should retry.
     */
    public function shouldRetry(): bool
    {
        return $this->retryAfter !== null && $this->retryAfter > 0;
    }

    /**
     * Get when to retry as DateTime.
     */
    public function getRetryTime(): ?\DateTime
    {
        if ($this->retryAfter === null) {
            return null;
        }

        return new \DateTime('+' . $this->retryAfter . ' seconds');
    }

    /**
     * Get user-friendly message with retry time.
     */
    public function getUserMessage(): string
    {
        if ($this->retryAfter) {
            $minutes = ceil($this->retryAfter / 60);
            return "Rate limit exceeded. Please try again in {$minutes} minute(s).";
        }

        return 'Too many requests. Please try again later.';
    }

    /**
     * Get suggestion for fixing this error.
     */
    public function getSuggestion(): string
    {
        return 'Implement exponential backoff in your application or reduce the frequency of API calls. '
             . 'Consider caching frequently accessed data.';
    }
}