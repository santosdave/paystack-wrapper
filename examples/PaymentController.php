<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SantosDave\Paystack\Facades\Paystack;
use SantosDave\Paystack\Exceptions\PaystackException;
use SantosDave\Paystack\Support\Helpers;

class PaymentController extends Controller
{
    /**
     * Show payment form.
     */
    public function showPaymentForm(): View
    {
        return view('payment.form');
    }

    /**
     * Initialize payment.
     */
    public function initializePayment(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:50',
        ]);

        try {
            $reference = Helpers::generateReference();

            $response = Paystack::transaction()->initialize([
                'email' => $request->email,
                'amount' => $request->amount,
                'currency' => 'NGN',
                'reference' => $reference,
                'callback_url' => route('payment.callback'),
                'metadata' => [
                    'user_id' => auth()->id(),
                    'order_id' => $request->order_id,
                ],
            ]);

            if ($response['status']) {
                // Store reference in session for verification
                session(['paystack_reference' => $reference]);

                return redirect($response['data']['authorization_url']);
            }

            return back()->with('error', $response['message']);
        } catch (PaystackException $e) {
            return back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback.
     */
    public function handleCallback(Request $request): RedirectResponse
    {
        $reference = $request->reference ?? session('paystack_reference');

        if (!$reference) {
            return redirect()->route('payment.form')
                ->with('error', 'No payment reference found.');
        }

        try {
            $response = Paystack::transaction()->verify($reference);

            if ($response['status'] && $response['data']['status'] === 'success') {
                $transaction = $response['data'];

                // Process successful payment
                $this->processSuccessfulPayment($transaction);

                return redirect()->route('payment.success')
                    ->with('success', 'Payment successful!')
                    ->with('transaction', $transaction);
            }

            return redirect()->route('payment.form')
                ->with('error', 'Payment verification failed.');
        } catch (PaystackException $e) {
            return redirect()->route('payment.form')
                ->with('error', 'Payment verification error: ' . $e->getMessage());
        }
    }

    /**
     * Process successful payment.
     */
    protected function processSuccessfulPayment(array $transaction): void
    {
        // Update order status
        // Send confirmation email
        // Store transaction details
        // etc.

        \Log::info('Payment successful', [
            'reference' => $transaction['reference'],
            'amount' => $transaction['amount'] / 100,
            'customer' => $transaction['customer']['email'],
        ]);
    }

    /**
     * Show success page.
     */
    public function success(): View
    {
        return view('payment.success');
    }

    /**
     * Refund a transaction.
     */
    public function refund(Request $request, string $transactionId): RedirectResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $response = Paystack::refund()->create([
                'transaction' => $transactionId,
                'amount' => $request->amount, // Optional: partial refund
                'merchant_note' => $request->reason,
            ]);

            if ($response['status']) {
                return back()->with('success', 'Refund initiated successfully.');
            }

            return back()->with('error', $response['message']);
        } catch (PaystackException $e) {
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}
