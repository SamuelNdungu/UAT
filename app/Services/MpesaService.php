<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MpesaService
{
    protected $http;

    public function __construct(Client $client = null)
    {
        $this->http = $client ?? new Client(['timeout' => 10]);
    }
    /**
     * Get an access token from MPESA (using curl to keep dependencies minimal).
     */
    public function getAccessToken()
    {
        $consumerKey = config('mpesa.consumer_key');
        $consumerSecret = config('mpesa.consumer_secret');
        $url = config('mpesa.oauth_url');

        if (!$consumerKey || !$consumerSecret || !$url) {
            Log::warning('MpesaService: missing configuration for access token');
            return null;
        }

        $credentials = base64_encode($consumerKey . ':' . $consumerSecret);
        $options = [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
            ],
        ];

        // simple retry loop
        $attempts = 0;
        while ($attempts < 3) {
            try {
                $resp = $this->http->request('GET', $url, $options);
                $body = (string) $resp->getBody();
                $data = json_decode($body, true);
                return $data['access_token'] ?? null;
            } catch (RequestException $e) {
                $attempts++;
                Log::warning('MpesaService getAccessToken attempt failed: ' . $e->getMessage());
                usleep(100000 * $attempts); // backoff
                continue;
            }
        }

        return null;
    }

    /**
     * Trigger STK Push with phone and amount. Returns parsed JSON or null.
     */
    public function triggerStkPush(string $phoneNumber, $amount)
    {
        $accessToken = $this->getAccessToken();
        $url = config('mpesa.stk_push_url');
        if (!$accessToken || !$url) {
            Log::warning('MpesaService: cannot trigger STK push, missing token or URL');
            return null;
        }

        $payload = [
            'BusinessShortCode' => config('mpesa.business_shortcode'),
            'Password' => config('mpesa.stk_password'),
            'Timestamp' => date('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => config('mpesa.business_shortcode'),
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => config('mpesa.callback_url'),
            'AccountReference' => config('app.name'),
            'TransactionDesc' => 'Payment'
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ],
            'json' => $payload,
        ];

        $attempts = 0;
        while ($attempts < 3) {
            try {
                $resp = $this->http->request('POST', $url, $options);
                $body = (string) $resp->getBody();
                return json_decode($body);
            } catch (RequestException $e) {
                $attempts++;
                Log::warning('MpesaService triggerStkPush attempt failed: ' . $e->getMessage());
                usleep(150000 * $attempts);
                continue;
            }
        }

        return null;
    }

    /**
     * Validate and parse a callback request. If a callback secret is configured, validate HMAC.
     * Returns array: ['valid' => bool, 'data' => mixed, 'reason' => string|null]
     */
    public function handleCallback(Request $request): array
    {
        $raw = $request->getContent();
        $data = json_decode($raw, true);

        // If a callback secret is configured, validate HMAC-SHA256 using header X-Mpesa-Signature
        $secret = config('mpesa.callback_secret');
        if ($secret) {
            $header = $request->header('X-Mpesa-Signature') ?? $request->header('x-signature') ?? null;
            if (!$header) {
                return ['valid' => false, 'data' => $data, 'reason' => 'missing signature header'];
            }

            // compute HMAC-SHA256 and base64 encode
            $computed = base64_encode(hash_hmac('sha256', $raw, $secret, true));
            if (!hash_equals($computed, $header)) {
                return ['valid' => false, 'data' => $data, 'reason' => 'signature_mismatch'];
            }
        }

        // Optionally perform other validations depending on provider (transaction codes etc.)
        return ['valid' => true, 'data' => $data, 'reason' => null];
    }

    /**
     * Normalize a phone number to a canonical format (e.g., 0712345678 or +254712345678 -> 254712345678)
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;
        // Remove non-digits
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (!$digits) return null;
        // If starts with '0' and length 10, replace leading 0 with country code 254
        if (strlen($digits) == 10 && strpos($digits, '0') === 0) {
            return '254' . substr($digits, 1);
        }
        // If starts with country code (e.g., '2547...') keep as is
        if (strlen($digits) >= 11 && substr($digits, 0, 3) === '254') {
            return $digits;
        }
        // If starts with international '+' removed earlier, handle '7xxxxxxxx' (9 digits)
        if (strlen($digits) == 9 && strpos($digits, '7') === 0) {
            return '254' . $digits;
        }
        // otherwise return digits as-is
        return $digits;
    }

    /**
     * Compare two amounts with configured tolerance
     */
    public static function amountsAreClose($a, $b): bool
    {
        $tolerance = floatval(config('mpesa.matching.amount_tolerance', 1.0));
        return abs(floatval($a) - floatval($b)) <= $tolerance;
    }
}
