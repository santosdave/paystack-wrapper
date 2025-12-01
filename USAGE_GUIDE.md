# Paystack Laravel Wrapper - Complete Usage Guide

## Table of Contents

1. [Getting Started](#getting-started)
2. [Common Use Cases](#common-use-cases)
3. [Advanced Features](#advanced-features)
4. [Best Practices](#best-practices)
5. [Troubleshooting](#troubleshooting)

## Getting Started

### Quick Start: Accept a Payment

The most common use case is accepting a one-time payment:

```php
// 1. Initialize the transaction
use SantosDave\Paystack\Facades\Paystack;

$response = Paystack::transaction()->initialize([
    'email' => 'customer@email.com',
    'amount' => 10000, // NGN 100.00
    'callback_url' => route('payment.callback'),
]);

// 2. Redirect user to payment page
return redirect($response['data']['authorization_url']);

// 3. Handle callback after payment
public function callback(Request $request)
{
    $reference = $request->reference;

    $response = Paystack::transaction()->verify($reference);

    if ($response['data']['status'] === 'success') {
        // Payment successful - fulfill order
        return redirect()->route('success');
    }

    return redirect()->route('failed');
}
```

## Common Use Cases

### 1. E-commerce Checkout

```php
use SantosDave\Paystack\Facades\Paystack;
use SantosDave\Paystack\Support\Helpers;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $request->total,
            'items' => $request->items,
        ]);

        $reference = Helpers::generateReference('ORDER');

        $response = Paystack::transaction()->initialize([
            'email' => auth()->user()->email,
            'amount' => $order->total,
            'reference' => $reference,
            'callback_url' => route('checkout.callback'),
            'metadata' => [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'cart_items' => count($request->items),
            ],
        ]);

        // Store reference for later verification
        $order->update(['payment_reference' => $reference]);

        return redirect($response['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $reference = $request->reference;
        $order = Order::where('payment_reference', $reference)->firstOrFail();

        try {
            $response = Paystack::transaction()->verify($reference);

            if ($response['data']['status'] === 'success') {
                // Update order status
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => $response['data']['id'],
                ]);

                // Send confirmation email
                Mail::to($order->user)->send(new OrderConfirmation($order));

                // Reduce inventory
                foreach ($order->items as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }

                return redirect()->route('order.success', $order);
            }

            $order->update(['status' => 'failed']);
            return redirect()->route('order.failed');

        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('order.failed');
        }
    }
}
```

### 2. Subscription Service

```php
class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);

        // Create plan in Paystack if not exists
        $paystackPlan = Paystack::plan()->create([
            'name' => $plan->name,
            'amount' => $plan->price,
            'interval' => $plan->interval, // monthly, yearly, etc.
        ]);

        // Create customer if not exists
        $customer = $this->getOrCreateCustomer(auth()->user());

        // Create subscription
        $subscription = Paystack::subscription()->create([
            'customer' => $customer['customer_code'],
            'plan' => $paystackPlan['data']['plan_code'],
        ]);

        // Store subscription in database
        auth()->user()->subscriptions()->create([
            'subscription_code' => $subscription['data']['subscription_code'],
            'plan_code' => $paystackPlan['data']['plan_code'],
            'customer_code' => $customer['customer_code'],
            'amount' => $plan->price,
            'status' => 'active',
        ]);

        return redirect()->route('subscription.success');
    }

    public function cancel($subscriptionCode)
    {
        $subscription = auth()->user()->subscriptions()
            ->where('subscription_code', $subscriptionCode)
            ->firstOrFail();

        // Get email token from subscription
        $emailToken = $subscription->email_token;

        Paystack::subscription()->disable([
            'code' => $subscriptionCode,
            'token' => $emailToken,
        ]);

        $subscription->cancel();

        return redirect()->route('subscriptions.index')
            ->with('success', 'Subscription cancelled successfully');
    }

    protected function getOrCreateCustomer($user)
    {
        if ($user->paystack_customer_code) {
            return ['customer_code' => $user->paystack_customer_code];
        }

        $response = Paystack::customer()->create([
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
        ]);

        $user->update([
            'paystack_customer_code' => $response['data']['customer_code']
        ]);

        return $response['data'];
    }
}
```

### 3. Split Payments (Marketplace)

```php
class MarketplacePaymentController extends Controller
{
    public function processPurchase(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $vendor = $product->vendor;

        // Create or get vendor subaccount
        $subaccount = $this->getOrCreateSubaccount($vendor);

        // Calculate split (e.g., 10% platform fee)
        $platformFee = $product->price * 0.10;

        $response = Paystack::transaction()->initialize([
            'email' => auth()->user()->email,
            'amount' => $product->price,
            'subaccount' => $subaccount['subaccount_code'],
            'transaction_charge' => $platformFee * 100, // in subunits
            'bearer' => 'account', // Charge goes to subaccount
            'metadata' => [
                'product_id' => $product->id,
                'vendor_id' => $vendor->id,
            ],
        ]);

        return redirect($response['data']['authorization_url']);
    }

    protected function getOrCreateSubaccount($vendor)
    {
        if ($vendor->paystack_subaccount_code) {
            return ['subaccount_code' => $vendor->paystack_subaccount_code];
        }

        $response = Paystack::subaccount()->create([
            'business_name' => $vendor->business_name,
            'settlement_bank' => $vendor->bank_code,
            'account_number' => $vendor->account_number,
            'percentage_charge' => 10, // 10% platform fee
            'description' => 'Vendor account for ' . $vendor->name,
        ]);

        $vendor->update([
            'paystack_subaccount_code' => $response['data']['subaccount_code']
        ]);

        return $response['data'];
    }
}
```

### 4. Recurring Charges (Saved Cards)

```php
class RecurringPaymentController extends Controller
{
    public function chargeCard(User $user, float $amount)
    {
        if (!$user->paystack_authorization_code) {
            throw new \Exception('No saved card for this user');
        }

        try {
            $response = Paystack::transaction()->charge([
                'email' => $user->email,
                'amount' => $amount,
                'authorization_code' => $user->paystack_authorization_code,
                'metadata' => [
                    'user_id' => $user->id,
                    'charge_type' => 'recurring',
                ],
            ]);

            if ($response['status']) {
                Log::info('Recurring charge successful', [
                    'user_id' => $user->id,
                    'amount' => $amount / 100,
                ]);

                return $response['data'];
            }

        } catch (\Exception $e) {
            Log::error('Recurring charge failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // Save authorization after first successful payment
    public function saveAuthorization($transactionData, User $user)
    {
        $user->update([
            'paystack_authorization_code' => $transactionData['authorization']['authorization_code'],
            'card_last4' => $transactionData['authorization']['last4'],
            'card_type' => $transactionData['authorization']['card_type'],
            'card_bank' => $transactionData['authorization']['bank'],
        ]);
    }
}
```

### 5. Bulk Payouts

```php
class PayoutController extends Controller
{
    public function processBulkPayouts(array $vendorPayouts)
    {
        $transfers = [];

        foreach ($vendorPayouts as $payout) {
            // Create or get transfer recipient
            $recipient = $this->getOrCreateRecipient($payout['vendor']);

            $transfers[] = [
                'amount' => $payout['amount'],
                'recipient' => $recipient['recipient_code'],
                'reason' => 'Weekly payout - ' . now()->format('Y-m-d'),
                'reference' => Helpers::generateReference('PAYOUT'),
            ];
        }

        // Initiate bulk transfer
        $response = Paystack::transfer()->bulkInitiate([
            'source' => 'balance',
            'transfers' => $transfers,
        ]);

        // Store transfer batch
        foreach ($response['data'] as $transfer) {
            Transfer::create([
                'vendor_id' => $this->findVendorByRecipient($transfer['recipient']),
                'transfer_code' => $transfer['transfer_code'],
                'amount' => $transfer['amount'],
                'status' => $transfer['status'],
            ]);
        }

        return $response;
    }

    protected function getOrCreateRecipient($vendor)
    {
        if ($vendor->transfer_recipient_code) {
            return ['recipient_code' => $vendor->transfer_recipient_code];
        }

        $response = Paystack::transferRecipient()->create([
            'type' => 'nuban',
            'name' => $vendor->name,
            'account_number' => $vendor->account_number,
            'bank_code' => $vendor->bank_code,
            'currency' => 'NGN',
        ]);

        $vendor->update([
            'transfer_recipient_code' => $response['data']['recipient_code']
        ]);

        return $response['data'];
    }
}
```

## Advanced Features

### Webhook Processing with Queue

```php
// WebhookController.php
public function handle(Request $request, WebhookHandler $handler)
{
    $payload = $handler->parse($request);

    // Store webhook for processing
    $webhook = PaystackWebhook::create([
        'event_type' => $handler->getEventType($payload),
        'paystack_id' => $payload['data']['id'] ?? null,
        'reference' => $payload['data']['reference'] ?? null,
        'payload' => $payload,
        'status' => 'pending',
    ]);

    // Dispatch job for async processing
    ProcessPaystackWebhook::dispatch($webhook);

    return response()->json(['status' => 'received'], 200);
}

// ProcessPaystackWebhook.php (Job)
class ProcessPaystackWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PaystackWebhook $webhook)
    {
    }

    public function handle()
    {
        try {
            $data = $this->webhook->payload['data'];

            match ($this->webhook->event_type) {
                'charge.success' => $this->handleChargeSuccess($data),
                'transfer.success' => $this->handleTransferSuccess($data),
                // ... other handlers
            };

            $this->webhook->markAsProcessed();

        } catch (\Exception $e) {
            $this->webhook->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}
```

### Custom Metadata and Tracking

```php
// Track conversion sources
$response = Paystack::transaction()->initialize([
    'email' => $user->email,
    'amount' => $amount,
    'metadata' => [
        'user_id' => $user->id,
        'source' => $request->header('referer'),
        'campaign' => session('campaign_id'),
        'device' => $request->userAgent(),
        'ip_address' => $request->ip(),
        'custom_fields' => [
            ['display_name' => 'Order ID', 'variable_name' => 'order_id', 'value' => $orderId],
            ['display_name' => 'Customer Type', 'variable_name' => 'customer_type', 'value' => 'premium'],
        ],
    ],
]);
```

## Best Practices

### 1. Always Verify Transactions

```php
// NEVER trust the callback URL alone
public function callback(Request $request)
{
    // Always verify with Paystack API
    $response = Paystack::transaction()->verify($request->reference);

    // Check status
    if ($response['data']['status'] !== 'success') {
        return redirect()->route('payment.failed');
    }

    // Verify amount matches
    if ($response['data']['amount'] !== $expectedAmount * 100) {
        Log::alert('Payment amount mismatch', [
            'expected' => $expectedAmount,
            'received' => $response['data']['amount'] / 100,
        ]);
        return redirect()->route('payment.failed');
    }

    // Process order
}
```

### 2. Use Database Transactions

```php
use Illuminate\Support\Facades\DB;

public function processPayment($transactionData)
{
    DB::transaction(function () use ($transactionData) {
        // Create transaction record
        $transaction = PaystackTransaction::create([...]);

        // Update order
        $order->markAsPaid();

        // Update inventory
        foreach ($order->items as $item) {
            $item->product->decrement('stock', $item->quantity);
        }

        // Send emails
        Mail::to($order->user)->send(new OrderConfirmation($order));
    });
}
```

### 3. Idempotency

```php
public function handleWebhook($data)
{
    // Check if already processed
    $existing = PaystackTransaction::where('reference', $data['reference'])->first();

    if ($existing && $existing->isSuccessful()) {
        Log::info('Duplicate webhook ignored', ['reference' => $data['reference']]);
        return;
    }

    // Process...
}
```

### 4. Rate Limiting

```php
use Illuminate\Support\Facades\RateLimiter;

public function initializePayment(Request $request)
{
    $key = 'payment-init:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 10)) {
        return back()->with('error', 'Too many payment attempts. Please try again later.');
    }

    RateLimiter::hit($key, 60); // 10 attempts per minute

    // Process payment...
}
```

## Troubleshooting

### Common Issues

#### 1. Invalid Signature Error (Webhooks)

```php
// Ensure webhook secret matches dashboard
// Check if request body is read multiple times
public function handle(Request $request)
{
    // Get raw content
    $payload = $request->getContent();

    // Verify signature
    $signature = $request->header('X-Paystack-Signature');
    $computed = hash_hmac('sha512', $payload, config('paystack.webhook_secret'));

    if (!hash_equals($signature, $computed)) {
        Log::error('Invalid webhook signature');
        return response()->json(['error' => 'Invalid signature'], 401);
    }
}
```

#### 2. SSL Verification Errors

```php
// NEVER disable in production!
// If you must test locally:
// .env
PAYSTACK_VERIFY_SSL=false // LOCAL ONLY!
```

#### 3. Amount Mismatch

```php
// Remember: amounts are in subunits
$amountInNaira = 100.00;
$amountInKobo = $amountInNaira * 100; // 10000

// The wrapper handles this automatically
Paystack::transaction()->initialize([
    'amount' => 100.00, // Will be converted to 10000 kobo
]);
```

#### 4. Transaction Not Found

```php
// Use try-catch for API calls
try {
    $transaction = Paystack::transaction()->verify($reference);
} catch (NotFoundException $e) {
    Log::error('Transaction not found', ['reference' => $reference]);
    return back()->with('error', 'Payment not found');
}
```

### Debugging

Enable logging to track issues:

```php
// .env
PAYSTACK_LOGGING_ENABLED=true
PAYSTACK_LOGGING_CHANNEL=daily

// Check logs
tail -f storage/logs/laravel.log | grep Paystack
```

## Performance Tips

1. **Cache frequently accessed data**:

```php
// Banks, countries are cached automatically
$banks = Paystack::miscellaneous()->listBanks(); // Cached for 24 hours
```

2. **Use queues for webhooks**:

```php
// Process webhooks asynchronously
ProcessPaystackWebhook::dispatch($webhook)->onQueue('webhooks');
```

3. **Batch operations**:

```php
// Use bulk APIs when available
Paystack::transfer()->bulkInitiate([...]);
Paystack::transferRecipient()->bulkCreate([...]);
```

## Security Checklist

- ✅ SSL verification enabled in production
- ✅ Webhook signatures verified
- ✅ Sensitive data redacted from logs
- ✅ API keys stored in environment variables
- ✅ Transaction amounts verified server-side
- ✅ Idempotency checks implemented
- ✅ Rate limiting configured
- ✅ Database transactions used for critical operations

---

For more examples, check the `/examples` directory in the package.
