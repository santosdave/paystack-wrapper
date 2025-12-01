<?php

namespace SantosDave\Paystack\Webhook;

use Illuminate\Http\Request;
use SantosDave\Paystack\Exceptions\PaystackException;

class WebhookHandler
{
    protected string $secret;

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret ?? config('paystack.webhook_secret');

        if (empty($this->secret)) {
            throw new PaystackException('Webhook secret is not configured.');
        }
    }

    /**
     * Verify webhook signature.
     *
     * @param Request $request
     * @return bool
     */
    public function verify(Request $request): bool
    {
        $signature = $request->header('X-Paystack-Signature');

        if (!$signature) {
            return false;
        }

        $computedSignature = hash_hmac('sha512', $request->getContent(), $this->secret);

        return hash_equals($signature, $computedSignature);
    }

    /**
     * Parse webhook payload.
     *
     * @param Request $request
     * @return array
     * @throws PaystackException
     */
    public function parse(Request $request): array
    {
        if (!$this->verify($request)) {
            throw new PaystackException('Invalid webhook signature.');
        }

        $payload = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PaystackException('Invalid JSON payload.');
        }

        return $payload;
    }

    /**
     * Get event type from webhook payload.
     *
     * @param array $payload
     * @return string|null
     */
    public function getEventType(array $payload): ?string
    {
        return $payload['event'] ?? null;
    }

    /**
     * Get event data from webhook payload.
     *
     * @param array $payload
     * @return array
     */
    public function getEventData(array $payload): array
    {
        return $payload['data'] ?? [];
    }

    /**
     * Check if event is of specific type.
     *
     * @param array $payload
     * @param string $eventType
     * @return bool
     */
    public function isEvent(array $payload, string $eventType): bool
    {
        return $this->getEventType($payload) === $eventType;
    }
}
