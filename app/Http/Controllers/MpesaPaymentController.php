<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;

class MpesaPaymentController extends Controller
{
    private $config;
    
    public function __construct()
    {
        $this->config = [
            'consumer_key' => config('mpesa.consumer_key'),
            'consumer_secret' => config('mpesa.consumer_secret'),
            'business_shortcode' => config('mpesa.business_shortcode'),
            'passkey' => config('mpesa.passkey'),
            'sandbox_url' => 'https://sandbox.safaricom.co.ke',
            'live_url' => 'https://api.safaricom.co.ke',
        ];
    }

    public function initiateMpesaPayment(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|regex:/^254[0-9]{9}$/',
                'amount' => 'required|numeric|min:1',
                'reference' => 'required|string',
                'payment_date' => 'required|string'
            ]);

            $access_token = $this->getAccessToken();

            if (!$access_token) {
                Log::error('MPESA: Failed to get access token');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to authenticate with M-PESA'
                ], 401);
            }

            // Create payment record before initiating STK Push
            $payment = Payment::create([
                'payment_amount' => $request->amount,
                'phone_number' => $request->phone,
                'reference' => $request->reference,
                'payment_date' => $request->payment_date,
                'status' => 'Pending'
            ]);

            $response = $this->sendStkPush($access_token, $request, $payment);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                // Update payment record with MPESA request details
                $payment->update([
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'checkout_request_id' => $response['CheckoutRequestID']
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment initiated successfully',
                    'data' => $response
                ]);
            }

            Log::error('MPESA STK Push failed', $response);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to initiate payment',
                'data' => $response
            ], 400);

        } catch (Exception $e) {
            Log::error('MPESA Payment Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    private function getAccessToken()
    {
        try {
            $credentials = base64_encode(
                $this->config['consumer_key'] . ':' . $this->config['consumer_secret']
            );

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $credentials,
            ])->get($this->config['sandbox_url'] . '/oauth/v1/generate?grant_type=client_credentials');

            if ($response->successful()) {
                return $response->json()['access_token'];
            }

            Log::error('MPESA Auth Error:', $response->json());
            return null;

        } catch (Exception $e) {
            Log::error('MPESA Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    private function sendStkPush($access_token, Request $request, Payment $payment)
    {
        try {
            
            $timestamp = date('YmdHis');
            $password = base64_encode(
                $this->config['business_shortcode'] . 
                $this->config['passkey'] . 
                $timestamp
            );

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',

            ])->post($this->config['sandbox_url'] . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $this->config['business_shortcode'],
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => $request->amount,
                'PartyA' => $request->phone,
                'PartyB' => $this->config['business_shortcode'],
                'PhoneNumber' => $request->phone,
                'CallBackURL' => route('mpesa.callback'),
                'AccountReference' => $request->reference,
                'TransactionDesc' => 'Payment for ' . $request->reference
            ]);
         dd ($response);
            return $response->json();

        } catch (Exception $e) {
            dd (" catch STKPush test");
            Log::error('MPESA STK Push Exception: ' . $e->getMessage());
            return [
                'ResponseCode' => '1',
                'ResponseDescription' => 'Failed to initiate STK push'
            ];
        }
    }

    public function handleMpesaCallback(Request $request)
    {
        try {
            Log::info('MPESA Callback Received:', $request->all());
            
            $callback = $request->input('Body.stkCallback');
            $resultCode = $callback['ResultCode'];
            $merchantRequestId = $callback['MerchantRequestID'];
            
            $payment = Payment::where('merchant_request_id', $merchantRequestId)->first();
            
            if (!$payment) {
                Log::error('MPESA: Payment record not found for MerchantRequestID: ' . $merchantRequestId);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            }

            if ($resultCode == 0) {
                $metadata = collect($callback['CallbackMetadata']['Item'])
                    ->keyBy(function ($item) {
                        return $item['Name'];
                    });

                $payment->update([
                    'status' => 'Completed',
                    'mpesa_receipt_number' => $metadata['MpesaReceiptNumber']['Value'],
                    'transaction_date' => Carbon::createFromFormat(
                        'YmdHis',
                        $metadata['TransactionDate']['Value']
                    ),
                    'phone_number' => $metadata['PhoneNumber']['Value']
                ]);

                // Trigger any success events or notifications here
                event(new MpesaPaymentSuccessful($payment));
            } else {
                $payment->update([
                    'status' => 'Failed',
                    'failure_reason' => $callback['ResultDesc']
                ]);

                // Trigger any failure events or notifications here
                event(new MpesaPaymentFailed($payment));
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);

        } catch (Exception $e) {
            Log::error('MPESA Callback Error: ' . $e->getMessage());
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }
    }
}