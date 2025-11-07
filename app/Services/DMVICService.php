<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DMVICService
{
    protected $client;
    protected $config;
    protected $token;
    protected $tokenExpiry;
    protected $tokenKey = 'dmvic_token';

    public function __construct()
    {
        $this->config = Config::get('services.dmvic');
        $this->client = new Client([
            'verify' => false, // Only for development
            'timeout' => 30,
            'http_errors' => false,
        ]);
        $this->token = cache($this->tokenKey);
        $this->tokenExpiry = cache('dmvic_token_expiry');
    }

    public function getToken()
    {
        if ($this->token && $this->tokenExpiry && now()->lt($this->tokenExpiry)) {
            return $this->token;
        }
        return $this->authenticate();
    }

    public function authenticate()
    {
        try {
            Log::debug('DMVIC Authentication Attempt', [
                'url' => $this->config['login_url'] ?? null,
                'cert_path' => $this->config['cert_path'] ?? null,
                'cert_exists' => isset($this->config['cert_path']) ? file_exists($this->config['cert_path']) : false
            ]);

            $response = $this->client->post($this->config['login_url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Version' => '1.8.0',
                ],
                'json' => [
                    'Username' => $this->config['username'],
                    'Password' => $this->config['password'],
                ],
                'cert' => $this->config['cert_path'],
                'verify' => false // Disable SSL verification for UAT/dev
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $data = json_decode($responseBody, true);

            Log::debug('DMVIC Authentication Response', [
                'status_code' => $statusCode,
                'response' => $data,
                'raw_response' => $responseBody
            ]);

            if ($statusCode !== 200) {
                throw new \Exception("Authentication failed with status code: {$statusCode}");
            }

            if (!isset($data['token'])) {
                throw new \Exception('Authentication failed: No token received in response');
            }

            $this->token = $data['token'];
            $this->tokenExpiry = now()->addSeconds($data['expires_in'] ?? 3600);
            cache([$this->tokenKey => $this->token], $this->tokenExpiry);
            cache(['dmvic_token_expiry' => $this->tokenExpiry], $this->tokenExpiry);

            Log::debug('DMVIC Authentication Successful', ['token' => substr($this->token, 0, 10) . '...']);
            return $this->token;

        } catch (\Exception $e) {
            Log::error('DMVIC Authentication Error: ' . $e->getMessage());
            throw new \Exception('Failed to authenticate with DMVIC API: ' . $e->getMessage());
        }
    }

    public function getStock($companyId = null)
    {
        // <-- your existing getStock() content left intact (no functional changes) -->
        try {
            $token = $this->getToken();
            $companyIds = $companyId ? [$companyId] : [14, 20]; // Default to both companies if none specified
            $allStocks = [];
            $byCompany = [];
            $totalStock = 0;
            
            $companyNames = [
                14 => 'APA Insurance',
                20 => 'First Assurance'
            ];
            $companyColors = [
                14 => 'rgba(255, 80, 5, 0.88)',  // Red for APA
                20 => 'rgba(15, 59, 153, 0.88)'   // Blue for First Assurance
            ];

            foreach ($companyIds as $companyId) {
                $attempt = 0;
                $maxAttempts = 2; // Only retry once if token refresh is needed
                $retried = false;
                do {
                    $response = $this->client->post($this->config['stock_url'], [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'ClientID' => $this->config['client_id'],
                            'Content-Type' => 'application/json',
                            'Version' => '1.8.0',
                        ],
                        'json' => [
                            'MemberCompanyID' => $companyId
                        ],
                        'cert' => $this->config['cert_path'],
                        'http_errors' => false,
                        'debug' => false
                    ]);

                    $statusCode = $response->getStatusCode();
                    $responseBody = $response->getBody()->getContents();
                    $data = json_decode($responseBody, true);

                    // Check for token expiration/invalid
                    $tokenExpired = false;
                    if ($statusCode === 401) {
                        $tokenExpired = true;
                    } elseif (is_array($data)) {
                        // Look for error messages in typical fields
                        $errorMsg = '';
                        if (isset($data['error'])) $errorMsg = $data['error'];
                        elseif (isset($data['message'])) $errorMsg = $data['message'];
                        elseif (isset($data['errors']) && is_array($data['errors'])) $errorMsg = implode(' ', $data['errors']);
                        if (is_string($errorMsg) && preg_match('/token.*(expired|invalid)/i', $errorMsg)) {
                            $tokenExpired = true;
                        }
                    }
                    if ($tokenExpired && !$retried) {
                        Log::warning("DMVIC token expired or invalid, refreshing token and retrying for company {$companyId}", [
                            'status_code' => $statusCode,
                            'response' => $data
                        ]);
                        $token = $this->authenticate();
                        $retried = true;
                        $attempt++;
                        continue;
                    }
                    // If not token expired, or already retried, break the loop
                    break;
                } while ($attempt < $maxAttempts);

                if ($statusCode !== 200) {
                    Log::error("DMVIC API Error for company {$companyId}", [
                        'status_code' => $statusCode,
                        'response' => $data
                    ]);
                    continue;
                }

                if (!isset($data['callbackObj']['MemberCompanyStock'])) {
                    Log::warning("No stock data found for company {$companyId}", ['response' => $data]);
                    continue;
                }

                $stocks = $data['callbackObj']['MemberCompanyStock'];
                $companyName = $companyNames[$companyId] ?? "Company {$companyId}";
                $companyTotal = 0;
                $companyStocks = [];

                foreach ($stocks as $stock) {
                    $stockItem = [
                        'CompanyName' => $companyName,
                        'CompanyId' => $companyId,
                        'CertificateClassificationID' => $stock['CertificateClassificationID'],
                        'ClassificationTitle' => $stock['ClassificationTitle'],
                        'Stock' => $stock['Stock'],
                        'LastUpdated' => now()->toDateTimeString(),
                        'Color' => $companyColors[$companyId] ?? 'rgba(75, 192, 192, 0.7)'
                    ];
                    $companyStocks[] = $stockItem;
                    $allStocks[] = $stockItem;
                    $companyTotal += $stock['Stock'];
                    $totalStock += $stock['Stock'];
                }

                $byCompany[$companyName] = [
                    'total' => $companyTotal,
                    'items' => $companyStocks,
                    'color' => $companyColors[$companyId] ?? 'rgba(75, 192, 192, 0.7)'
                ];
            }

            return [
                'success' => true,
                'stocks' => $allStocks,
                'totalStock' => $totalStock,
                'byCompany' => $byCompany,
                'companyColors' => $companyColors
            ];

        } catch (\Exception $e) {
            Log::error('DMVIC Stock Error: ' . $e->getMessage());
            throw new \Exception('Failed to get stock data: ' . $e->getMessage());
        }
    }

    protected function getCompanyName($companyId)
    {
        $companies = [
            '14' => 'APA',
            '20' => 'First Assurance'
        ];
        return $companies[$companyId] ?? 'Unknown';
    }

    /**
     * Issue a certificate by calling the DMVIC issuance endpoint.
     *
     * @param string $endpoint e.g. 'IssuanceTypeACertificate' or full path
     * @param array $payload  array payload to send (keys must match DMVIC expectations)
     * @return array decoded JSON response or ['success'=>false,'error'=>...]
     */
    public function issueCertificate(string $endpoint, array $payload): array
    {
        // Normalize endpoint into a full URL if needed
        $base = $this->config['base_issue_url'] ?? 'https://uat-api.dmvic.com/api/V5/IntermediaryIntegration/';
        // Accept either full URL or short endpoint
        if (filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $url = $endpoint;
        } else {
            // ensure trailing slash on base
            $base = rtrim($base, '/') . '/';
            $url = $base . ltrim($endpoint, '/');
        }

        // Try calling API; if token fails, refresh once and retry
        $attempt = 0;
        $maxAttempts = 2;
        $lastError = null;

        do {
            $token = null;
            try {
                $token = $this->getToken();
            } catch (\Exception $e) {
                Log::error('DMVIC token fetch error before issuing certificate: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Failed to authenticate with DMVIC before issuing certificate: ' . $e->getMessage()
                ];
            }

            try {
                $response = $this->sendApiRequest('POST', $url, $payload, $token);
                // if response indicates token issue (401) we'll retry after re-auth
                $status = $response['__meta__']['status'] ?? ($response['status'] ?? null);

                // Accept both structured array from sendApiRequest or raw decoded
                if (is_array($response) && isset($response['success'])) {
                    // normal
                    return $response;
                }

                // if sendApiRequest returned decoded JSON directly, return it
                return $response;
            } catch (\Exception $e) {
                $lastError = $e;
                Log::warning("DMVIC issueCertificate attempt {$attempt} failed: " . $e->getMessage(), [
                    'url' => $url,
                    'payload' => $payload,
                    'attempt' => $attempt
                ]);

                // If error looks like auth / token problem, force refresh and retry
                if (strpos(strtolower($e->getMessage()), '401') !== false || preg_match('/token.*(expired|invalid)/i', $e->getMessage())) {
                    try {
                        $this->authenticate();
                    } catch (\Exception $inner) {
                        Log::error('Failed to refresh DMVIC token: ' . $inner->getMessage());
                        return [
                            'success' => false,
                            'error' => 'Failed to refresh DMVIC token: ' . $inner->getMessage()
                        ];
                    }
                }

                $attempt++;
                if ($attempt < $maxAttempts) {
                    continue;
                }

                break;
            }
        } while ($attempt < $maxAttempts);

        // final failure
        Log::error('DMVIC issueCertificate final failure', [
            'url' => $url,
            'payload' => $payload,
            'last_error' => $lastError ? $lastError->getMessage() : null
        ]);

        return [
            'success' => false,
            'error' => $lastError ? $lastError->getMessage() : 'Unknown error while calling DMVIC issuance endpoint.'
        ];
    }

    /**
     * Generic helper to call DMVIC endpoints. Returns decoded array on success, throws on failure.
     *
     * @param string $method HTTP method (POST)
     * @param string $url full URL
     * @param array $payload
     * @param string $token
     * @return array decoded JSON on success
     * @throws \Exception on HTTP/network errors or when unable to decode response
     */
    protected function sendApiRequest(string $method, string $url, array $payload, string $token): array
    {
        Log::debug('DMVIC sendApiRequest', ['method' => $method, 'url' => $url, 'payload' => $payload]);

        $response = $this->client->request($method, $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'ClientID' => $this->config['client_id'],
                'Content-Type' => 'application/json',
                'Version' => '1.8.0',
            ],
            'json' => $payload,
            'cert' => $this->config['cert_path'],
            'http_errors' => false,
            'debug' => false,
            'verify' => false // development only
        ]);

        $statusCode = $response->getStatusCode();
        $raw = $response->getBody()->getContents();
        $decoded = json_decode($raw, true);

        Log::debug('DMVIC API raw response', [
            'url' => $url,
            'status' => $statusCode,
            'raw' => $raw,
            'decoded' => $decoded
        ]);

        if ($statusCode === 401) {
            // token problem, throw to allow re-auth/retry
            throw new \Exception('401 Unauthorized from DMVIC API - token may be invalid or expired');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            // return structured error for the controller to log and inspect
            $msg = "DMVIC API returned HTTP {$statusCode}";
            if (is_array($decoded) && (isset($decoded['message']) || isset($decoded['error']))) {
                $msg .= ': ' . ($decoded['message'] ?? $decoded['error']);
            }
            throw new \Exception($msg);
        }

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to decode DMVIC API JSON response: ' . json_last_error_msg());
        }

        // return decoded response directly so controller can extract callbackObj etc.
        return $decoded;
    }

    /**
     * Check for double insurance issuance
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function checkDoubleIssuance($data)
    {
        try {
            $token = $this->authenticate();
            
            $response = $this->client->post('https://uat-api.dmvic.com/api/v5/Integration/ValidateDoubleInsurance', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'ClientID' => $this->config['client_id'],
                    'Content-Type' => 'application/json',
                    'Version' => '1.0.0',
                ],
                'json' => [
                    'vehicleregistrationnumber' => $data['vehicleregistrationnumber'] ?? '',
                    'chassisnumber' => $data['chassisnumber'] ?? '',
                    'policystartdate' => $data['policystartdate'],
                    'policyenddate' => $data['policyenddate']
                ],
                'cert' => $this->config['cert_path'],
                'http_errors' => false,
                'debug' => false,
                'verify' => false // Only for development, remove in production
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();
            $result = json_decode($responseBody, true);

            if ($statusCode !== 200) {
                Log::error('DMVIC Double Issuance Check Error', [
                    'status_code' => $statusCode,
                    'response' => $result,
                    'request_data' => $data
                ]);
                
                throw new \Exception('Failed to check double issuance: ' . ($result['message'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            Log::error('DMVIC Double Issuance Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
