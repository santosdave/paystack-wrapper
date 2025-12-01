# Laravel Paystack Wrapper

A comprehensive, secure, and production-ready Laravel wrapper for the Paystack payment gateway API.

## Features

- ✅ **Complete API Coverage** - All Paystack API endpoints covered
- 🔒 **Secure by Default** - SSL verification, webhook signature validation
- 🚀 **Performance Optimized** - Built-in caching for frequently accessed data
- 📝 **Comprehensive Logging** - Track all API interactions with sensitive data redaction
- 🎯 **Type-Safe** - Proper exception handling with custom exception classes
- 🧪 **Fully Tested** - Extensive test coverage
- 📦 **Framework Agnostic Core** - Easy integration with Laravel
- 🔄 **Automatic Conversions** - Currency amount conversion to/from subunits
- 🎨 **Fluent API** - Clean, intuitive method chaining
- 🔔 **Webhook Support** - Secure webhook handling with signature verification

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Transactions](#transactions)
  - [Customers](#customers)
  - [Plans & Subscriptions](#plans--subscriptions)
  - [Subaccounts](#subaccounts)
  - [Transfers](#transfers)
  - [Refunds](#refunds)
  - [Disputes](#disputes)
  - [Verification](#verification)
  - [Payment Pages](#payment-pages)
  - [Payment Requests](#payment-requests)
  - [Dedicated Accounts](#dedicated-accounts)
  - [Webhooks](#webhooks)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- GuzzleHTTP 7.5 or higher

## Installation

Install via Composer:

```bash
composer require santosdave/laravel-paystack
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=paystack-config
```

## Configuration

Add your Paystack credentials to `.env`:

```env
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxx
PAYSTACK_MERCHANT_EMAIL=your@email.com
PAYSTACK_CURRENCY=NGN
PAYSTACK_WEBHOOK_SECRET=your_webhook_secret

# Optional
PAYSTACK_LOGGING_ENABLED=true
PAYSTACK_CACHE_ENABLED=true
```

## Usage

### Transactions

```php
use SantosDave\Paystack\Facades\Paystack;

// Initialize a transaction
$transaction = Paystack::transaction()->initialize([
    'email' => 'customer@email.com',
    'amount' => 10000, // NGN 100.00 (automatically converted to kobo)
    'currency' => 'NGN',
    'callback_url' => route('payment.callback'),
    'metadata' => ['order_id' => 'ORD123'],
]);

// Redirect user to payment page
return redirect($transaction['data']['authorization_url']);

// Verify transaction
$verification = Paystack::transaction()->verify($reference);

// List transactions
$transactions = Paystack::transaction()->list([
    'perPage' => 50,
    'page' => 1,
    'status' => 'success',
]);

// Fetch single transaction
$transaction = Paystack::transaction()->fetch($transactionId);

// Charge authorization (saved card)
$charge = Paystack::transaction()->charge([
    'email' => 'customer@email.com',
    'amount' => 5000,
    'authorization_code' => 'AUTH_xxx',
]);

// Get transaction timeline
$timeline = Paystack::transaction()->timeline($idOrReference);

// Export transactions
$export = Paystack::transaction()->export([
    'from' => '2024-01-01',
    'to' => '2024-12-31',
]);

// Partial debit
$debit = Paystack::transaction()->partialDebit([
    'authorization_code' => 'AUTH_xxx',
    'currency' => 'NGN',
    'amount' => 5000,
    'email' => 'customer@email.com',
]);
```

### Customers

```php
// Create customer
$customer = Paystack::customer()->create([
    'email' => 'customer@email.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'phone' => '+2348012345678',
]);

// List customers
$customers = Paystack::customer()->list();

// Fetch customer
$customer = Paystack::customer()->fetch($customerCode);

// Update customer
$updated = Paystack::customer()->update($customerCode, [
    'first_name' => 'Jane',
    'metadata' => ['vip' => true],
]);

// Validate customer (KYC)
$validation = Paystack::customer()->validate($customerCode, [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'type' => 'bank_account',
    'value' => '0123456789',
    'country' => 'NG',
    'bvn' => '12345678901',
    'bank_code' => '007',
    'account_number' => '0123456789',
]);

// Whitelist/blacklist customer
$action = Paystack::customer()->setRiskAction([
    'customer' => 'CUS_xxx',
    'risk_action' => 'allow', // or 'deny'
]);

// Deactivate authorization
Paystack::customer()->deactivateAuthorization($authorizationCode);
```

### Plans & Subscriptions

```php
// Create plan
$plan = Paystack::plan()->create([
    'name' => 'Monthly Premium',
    'amount' => 5000, // NGN 50.00
    'interval' => 'monthly', // daily, weekly, monthly, yearly
    'description' => 'Premium subscription plan',
    'send_invoices' => true,
    'send_sms' => true,
]);

// List plans
$plans = Paystack::plan()->list();

// Fetch plan
$plan = Paystack::plan()->fetch($planCode);

// Update plan
$updated = Paystack::plan()->update($planCode, [
    'name' => 'Updated Plan Name',
    'amount' => 7500,
]);

// Create subscription
$subscription = Paystack::subscription()->create([
    'customer' => 'CUS_xxx',
    'plan' => 'PLN_xxx',
]);

// List subscriptions
$subscriptions = Paystack::subscription()->list();

// Fetch subscription
$subscription = Paystack::subscription()->fetch($subscriptionCode);

// Enable subscription
$enabled = Paystack::subscription()->enable([
    'code' => $subscriptionCode,
    'token' => $emailToken,
]);

// Disable subscription
$disabled = Paystack::subscription()->disable([
    'code' => $subscriptionCode,
    'token' => $emailToken,
]);

// Generate update subscription link
$link = Paystack::subscription()->generateUpdateLink($subscriptionCode);

// Send update subscription link via email
$sent = Paystack::subscription()->sendUpdateLink($subscriptionCode);
```

### Subaccounts

```php
// Create subaccount
$subaccount = Paystack::subaccount()->create([
    'business_name' => 'ABC Corp',
    'settlement_bank' => '058',
    'account_number' => '0123456789',
    'percentage_charge' => 10.5,
    'description' => 'Marketplace vendor account',
]);

// List subaccounts
$subaccounts = Paystack::subaccount()->list();

// Fetch subaccount
$subaccount = Paystack::subaccount()->fetch($subaccountCode);

// Update subaccount
$updated = Paystack::subaccount()->update($subaccountCode, [
    'business_name' => 'Updated Business Name',
    'percentage_charge' => 15.0,
    'active' => true,
]);
```

### Transfers

```php
// Create transfer recipient
$recipient = Paystack::transferRecipient()->create([
    'type' => 'nuban',
    'name' => 'John Doe',
    'account_number' => '0123456789',
    'bank_code' => '058',
    'currency' => 'NGN',
]);

// List transfer recipients
$recipients = Paystack::transferRecipient()->list();

// Initiate transfer
$transfer = Paystack::transfer()->initiate([
    'source' => 'balance',
    'amount' => 50000, // NGN 500.00
    'recipient' => 'RCP_xxx',
    'reason' => 'Payment for services',
]);

// Bulk transfers
$bulk = Paystack::transfer()->bulkInitiate([
    'source' => 'balance',
    'transfers' => [
        [
            'amount' => 50000,
            'recipient' => 'RCP_xxx1',
            'reason' => 'Payment 1',
        ],
        [
            'amount' => 30000,
            'recipient' => 'RCP_xxx2',
            'reason' => 'Payment 2',
        ],
    ],
]);

// List transfers
$transfers = Paystack::transfer()->list();

// Fetch transfer
$transfer = Paystack::transfer()->fetch($transferCode);

// Finalize transfer (with OTP)
$finalized = Paystack::transfer()->finalize([
    'transfer_code' => 'TRF_xxx',
    'otp' => '123456',
]);

// Verify transfer
$verification = Paystack::transfer()->verify($reference);

// Disable OTP for transfers
$disabled = Paystack::transfer()->disableOtp();

// Check balance
$balance = Paystack::transferControl()->checkBalance();
```

### Refunds

```php
// Create refund
$refund = Paystack::refund()->create([
    'transaction' => $transactionId,
    'amount' => 5000, // Optional: partial refund
    'merchant_note' => 'Customer requested refund',
    'customer_note' => 'Refund for cancelled order',
]);

// List refunds
$refunds = Paystack::refund()->list([
    'reference' => $transactionReference,
]);

// Fetch refund
$refund = Paystack::refund()->fetch($reference);
```

### Disputes

```php
// List disputes
$disputes = Paystack::dispute()->list([
    'status' => 'pending',
]);

// Fetch dispute
$dispute = Paystack::dispute()->fetch($disputeId);

// Update dispute
$updated = Paystack::dispute()->update($disputeId, [
    'refund_amount' => 5000,
]);

// Add evidence
$evidence = Paystack::dispute()->addEvidence($disputeId, [
    'customer_email' => 'customer@email.com',
    'customer_name' => 'John Doe',
    'customer_phone' => '+2348012345678',
    'service_details' => 'Product delivered on 2024-01-15',
]);

// Get upload URL for evidence
$url = Paystack::dispute()->getUploadUrl($disputeId);

// Resolve dispute
$resolved = Paystack::dispute()->resolve($disputeId, [
    'resolution' => 'merchant-accepted',
    'message' => 'Refund processed',
    'refund_amount' => 5000,
    'uploaded_filename' => 'evidence.pdf',
]);

// Export disputes
$export = Paystack::dispute()->export();
```

### Verification

```php
// Resolve account number
$account = Paystack::verification()->resolveAccountNumber([
    'account_number' => '0123456789',
    'bank_code' => '058',
]);

// Validate account
$validation = Paystack::verification()->validateAccount([
    'account_name' => 'John Doe',
    'account_number' => '0123456789',
    'account_type' => 'personal',
    'bank_code' => '058',
    'country_code' => 'NG',
    'document_type' => 'identityNumber',
    'document_number' => 'ABC123456',
]);

// Resolve card BIN
$cardInfo = Paystack::verification()->resolveCardBin('539983');

// Match BVN
$bvnMatch = Paystack::verification()->matchBvn([
    'bvn' => '12345678901',
    'account_number' => '0123456789',
    'bank_code' => '058',
    'first_name' => 'John',
    'last_name' => 'Doe',
]);

// List supported banks
$banks = Paystack::miscellaneous()->listBanks(['country' => 'nigeria']);

// List countries
$countries = Paystack::miscellaneous()->listCountries();
```

### Payment Pages

```php
// Create payment page
$page = Paystack::paymentPage()->create([
    'name' => 'Product Purchase',
    'description' => 'Purchase our amazing product',
    'amount' => 50000, // Optional: fixed amount
    'slug' => 'product-purchase',
]);

// List payment pages
$pages = Paystack::paymentPage()->list();

// Fetch payment page
$page = Paystack::paymentPage()->fetch($pageIdOrSlug);

// Update payment page
$updated = Paystack::paymentPage()->update($pageIdOrSlug, [
    'name' => 'Updated Page Name',
    'active' => true,
]);

// Check slug availability
$available = Paystack::paymentPage()->checkSlugAvailability('my-slug');
```

### Payment Requests

```php
// Create payment request (invoice)
$request = Paystack::paymentRequest()->create([
    'customer' => 'CUS_xxx',
    'amount' => 100000,
    'due_date' => '2024-12-31',
    'description' => 'Invoice for services',
    'line_items' => [
        ['name' => 'Service 1', 'amount' => 50000],
        ['name' => 'Service 2', 'amount' => 50000],
    ],
    'send_notification' => true,
]);

// List payment requests
$requests = Paystack::paymentRequest()->list();

// Fetch payment request
$request = Paystack::paymentRequest()->fetch($requestCode);

// Verify payment request
$verification = Paystack::paymentRequest()->verify($requestCode);

// Send notification
$sent = Paystack::paymentRequest()->sendNotification($requestCode);

// Finalize draft
$finalized = Paystack::paymentRequest()->finalize($requestCode);

// Archive payment request
$archived = Paystack::paymentRequest()->archive($requestCode);
```

### Dedicated Accounts

```php
// Create dedicated virtual account
$account = Paystack::dedicatedAccount()->create([
    'customer' => 'CUS_xxx',
    'preferred_bank' => 'wema-bank',
]);

// Assign dedicated account
$assigned = Paystack::dedicatedAccount()->assign([
    'email' => 'customer@email.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'phone' => '+2348012345678',
    'preferred_bank' => 'wema-bank',
    'country' => 'NG',
]);

// List dedicated accounts
$accounts = Paystack::dedicatedAccount()->list();

// Fetch dedicated account
$account = Paystack::dedicatedAccount()->fetch($accountId);

// Deactivate dedicated account
$deactivated = Paystack::dedicatedAccount()->deactivate($accountId);

// Fetch available providers
$providers = Paystack::dedicatedAccount()->fetchProviders();
```

### Webhooks

Set up webhook handling in your routes:

```php
// routes/web.php
use App\Http\Controllers\WebhookController;
use SantosDave\Paystack\Http\Middleware\VerifyWebhookSignature;

Route::post('/webhook/paystack', [WebhookController::class, 'handle'])
    ->middleware(VerifyWebhookSignature::class);
```

Create a webhook controller:

```php
use SantosDave\Paystack\Webhook\WebhookHandler;

class WebhookController extends Controller
{
    public function handle(Request $request, WebhookHandler $handler)
    {
        $payload = $handler->parse($request);
        $eventType = $handler->getEventType($payload);
        $data = $handler->getEventData($payload);

        match ($eventType) {
            'charge.success' => $this->handleChargeSuccess($data),
            'transfer.success' => $this->handleTransferSuccess($data),
            'subscription.create' => $this->handleSubscriptionCreated($data),
            // ... handle other events
        };

        return response()->json(['status' => 'success']);
    }
}
```

## Error Handling

The wrapper provides specific exception classes:

```php
use SantosDave\Paystack\Exceptions\PaystackException;
use SantosDave\Paystack\Exceptions\AuthenticationException;
use SantosDave\Paystack\Exceptions\ValidationException;
use SantosDave\Paystack\Exceptions\NotFoundException;
use SantosDave\Paystack\Exceptions\RateLimitException;

try {
    $transaction = Paystack::transaction()->initialize([
        'email' => 'customer@email.com',
        'amount' => 10000,
    ]);
} catch (AuthenticationException $e) {
    // Invalid API key
    Log::error('Authentication failed: ' . $e->getMessage());
} catch (ValidationException $e) {
    // Invalid parameters
    $errors = $e->getErrors();
} catch (RateLimitException $e) {
    // Rate limit exceeded
} catch (PaystackException $e) {
    // General Paystack error
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test -- --coverage
```

## Security Features

- ✅ **SSL Verification**: Enforced in production environments
- ✅ **Webhook Signature Validation**: Verify webhook authenticity
- ✅ **Sensitive Data Redaction**: Automatic redaction in logs
- ✅ **Secure Headers**: Proper authentication headers
- ✅ **Input Validation**: Required field validation
- ✅ **Rate Limiting**: Graceful handling of rate limits

## Performance

- ✅ **Response Caching**: Automatic caching for GET requests
- ✅ **Connection Pooling**: Efficient HTTP connection management
- ✅ **Timeout Configuration**: Configurable request timeouts

## Currency Support

- **NGN** (Nigerian Naira) - Min: ₦50.00
- **USD** (US Dollar) - Min: $2.00
- **GHS** (Ghanaian Cedi) - Min: ₵0.10
- **ZAR** (South African Rand) - Min: R1.00
- **KES** (Kenyan Shilling) - Min: Ksh. 3.00
- **XOF** (West African CFA Franc) - Min: XOF 1.00

All amounts are automatically converted to subunits.

## Helper Utilities

```php
use SantosDave\Paystack\Support\Helpers;

// Generate unique reference
$reference = Helpers::generateReference('ORDER');

// Format amount
$formatted = Helpers::formatAmount(10000, 'NGN'); // ₦ 100.00

// Get supported currencies
$currencies = Helpers::getSupportedCurrencies();

// Validate amount
$isValid = Helpers::validateAmount(100, 'NGN'); // true

// Get webhook event types
$events = Helpers::getWebhookEventTypes();
```

## License

MIT License

## Support

For issues and questions, please use the GitHub issue tracker.

## Credits

Built with ❤️ for the Laravel community
