<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use SantosDave\Paystack\Webhook\WebhookHandler;
use SantosDave\Paystack\Exceptions\PaystackException;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WebhookHandler $webhookHandler;

    public function __construct(WebhookHandler $webhookHandler)
    {
        $this->webhookHandler = $webhookHandler;
    }

    /**
     * Handle Paystack webhook.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $this->webhookHandler->parse($request);
            $eventType = $this->webhookHandler->getEventType($payload);
            $data = $this->webhookHandler->getEventData($payload);

            Log::info('Paystack webhook received', [
                'event' => $eventType,
                'data' => $data,
            ]);

            // Route to appropriate handler
            match ($eventType) {
                'charge.success' => $this->handleChargeSuccess($data),
                'charge.dispute.create' => $this->handleDisputeCreated($data),
                'charge.dispute.resolve' => $this->handleDisputeResolved($data),
                'transfer.success' => $this->handleTransferSuccess($data),
                'transfer.failed' => $this->handleTransferFailed($data),
                'transfer.reversed' => $this->handleTransferReversed($data),
                'subscription.create' => $this->handleSubscriptionCreated($data),
                'subscription.disable' => $this->handleSubscriptionDisabled($data),
                'refund.processed' => $this->handleRefundProcessed($data),
                'customeridentification.success' => $this->handleCustomerIdentificationSuccess($data),
                default => $this->handleUnknownEvent($eventType, $data),
            };

            return response()->json(['status' => 'success'], 200);
        } catch (PaystackException $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle successful charge.
     */
    protected function handleChargeSuccess(array $data): void
    {
        Log::info('Charge successful', [
            'reference' => $data['reference'],
            'amount' => $data['amount'] / 100,
            'customer' => $data['customer']['email'],
        ]);

        // Update order status
        // Send confirmation email
        // Store transaction details
    }

    /**
     * Handle dispute created.
     */
    protected function handleDisputeCreated(array $data): void
    {
        Log::warning('Dispute created', [
            'reference' => $data['transaction']['reference'],
            'reason' => $data['reason'],
            'amount' => $data['amount'] / 100,
        ]);

        // Notify admin
        // Prepare evidence
    }

    /**
     * Handle dispute resolved.
     */
    protected function handleDisputeResolved(array $data): void
    {
        Log::info('Dispute resolved', [
            'reference' => $data['transaction']['reference'],
            'resolution' => $data['resolution'],
        ]);

        // Update records
        // Notify relevant parties
    }

    /**
     * Handle successful transfer.
     */
    protected function handleTransferSuccess(array $data): void
    {
        Log::info('Transfer successful', [
            'reference' => $data['reference'],
            'amount' => $data['amount'] / 100,
            'recipient' => $data['recipient']['name'],
        ]);

        // Update payout records
        // Send confirmation
    }

    /**
     * Handle failed transfer.
     */
    protected function handleTransferFailed(array $data): void
    {
        Log::error('Transfer failed', [
            'reference' => $data['reference'],
            'reason' => $data['reason'] ?? 'Unknown',
        ]);

        // Notify admin
        // Retry logic if applicable
    }

    /**
     * Handle reversed transfer.
     */
    protected function handleTransferReversed(array $data): void
    {
        Log::warning('Transfer reversed', [
            'reference' => $data['reference'],
            'amount' => $data['amount'] / 100,
        ]);

        // Update records
        // Notify relevant parties
    }

    /**
     * Handle subscription created.
     */
    protected function handleSubscriptionCreated(array $data): void
    {
        Log::info('Subscription created', [
            'code' => $data['subscription_code'],
            'customer' => $data['customer']['email'],
            'plan' => $data['plan']['name'],
        ]);

        // Update subscription records
        // Send welcome email
    }

    /**
     * Handle subscription disabled.
     */
    protected function handleSubscriptionDisabled(array $data): void
    {
        Log::info('Subscription disabled', [
            'code' => $data['subscription_code'],
            'customer' => $data['customer']['email'],
        ]);

        // Update subscription status
        // Send cancellation confirmation
    }

    /**
     * Handle refund processed.
     */
    protected function handleRefundProcessed(array $data): void
    {
        Log::info('Refund processed', [
            'reference' => $data['transaction']['reference'],
            'amount' => $data['amount'] / 100,
        ]);

        // Update transaction records
        // Send refund confirmation
    }

    /**
     * Handle customer identification success.
     */
    protected function handleCustomerIdentificationSuccess(array $data): void
    {
        Log::info('Customer identification successful', [
            'customer' => $data['customer_code'],
        ]);

        // Update customer verification status
    }

    /**
     * Handle unknown event.
     */
    protected function handleUnknownEvent(string $eventType, array $data): void
    {
        Log::warning('Unknown webhook event', [
            'event' => $eventType,
            'data' => $data,
        ]);
    }
}
