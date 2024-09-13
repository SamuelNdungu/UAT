<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Laravel HTTP client
use App\Models\Payment; // Assuming you have a Payment model
use Carbon\Carbon;

class MpesaPaymentController extends Controller
{
    private $consumerKey = "vjkvnAV8EZNTAWQ5rAt16OdWeks1PP3lJ3qS3cHdiGvtCBAa";
    private $consumerSecret = "3zmVfeY3PhRLtA2I4VLk71DTihx4HE28EscrHvGOjlOod2GU3inBSR0011Qw5eU1";
    private $BusinessShortCode = 'N/A';
    private $Passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';

    public function initiateMpesaPayment(Request $request)
    {
        // Step 1: Generate an access token
        $access_token = $this->getAccessToken();

        if (!$access_token) {
            return response()->json(['error' => 'Failed to authenticate with M-PESA'], 401);
        }

        // Step 2: Initiate STK Push
        $response = $this->sendStkPush($access_token, $request);

        return response()->json($response);
    }

    public function getAccessToken()
    {
        $consumerKey = config('mpesa.consumer_key');
        $consumerSecret = config('mpesa.consumer_secret');
        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);

        // This URL should be the token URL, not the STK Push request URL
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response);
        if (isset($data->access_token)) {
            return $data->access_token;
        } else {
            return null;
        }
    }

    public function sendStkPush($access_token, Request $request)
    {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ];

        $body = json_encode([
            "BusinessShortCode" => 174379,
            "Password" => base64_encode(174379 . $this->Passkey . date('YmdHis')),
            "Timestamp" => date('YmdHis'),
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => 1,
            "PartyA" => $request->input('phone'), // Dynamic input
            "PartyB" => 174379,
            "PhoneNumber" => $request->input('phone'), // Dynamic input
            "CallBackURL" => "https://mydomain.com/path", // Modify with actual callback URL
            "AccountReference" => "BimaConnect",
            "TransactionDesc" => "Payment of X"
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function handleMpesaCallback(Request $request)
    {
        $data = json_decode($request->getContent()); // Get the JSON payload sent by M-PESA

        \Log::info('M-PESA Callback Data:', (array)$data);

        // Assuming $data contains 'ResultCode' and 'ResultDesc' for simplification
        $resultCode = $data->Body->stkCallback->ResultCode;
        $resultDesc = $data->Body->stkCallback->ResultDesc;
        $merchantRequestId = $data->Body->stkCallback->MerchantRequestID;
        $checkoutRequestId = $data->Body->stkCallback->CheckoutRequestID;
        $amount = $data->Body->stkCallback->CallbackMetadata->Item[0]->Value;
        $mpesaReceiptNumber = $data->Body->stkCallback->CallbackMetadata->Item[1]->Value;
        $transactionDate = $data->Body->stkCallback->CallbackMetadata->Item[3]->Value;
        $phoneNumber = $data->Body->stkCallback->CallbackMetadata->Item[4]->Value;

        // Find the payment or order using the MerchantRequestID or CheckoutRequestID
        $payment = Payment::where('merchant_request_id', $merchantRequestId)->first();

        if ($resultCode == 0) {
            // Transaction was successful
            $payment->update([
                'status' => 'Completed',
                'mpesa_receipt_number' => $mpesaReceiptNumber,
                'transaction_date' => Carbon::createFromFormat('YmdHis', $transactionDate),
                'phone_number' => $phoneNumber
            ]);
        } else {
            // Transaction failed
            $payment->update([
                'status' => 'Failed',
                'failure_reason' => $resultDesc
            ]);
        }

        // Always respond to M-PESA
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Confirmation received successfully']);
    }
}
