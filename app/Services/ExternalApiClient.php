<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for the external school data API.
 * Handles authentication, retry logic, and error handling.
 */
class ExternalApiClient
{
    protected string $baseUrl;
    protected string $secret;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('external_api.base_url'), '/');
        $this->secret = config('external_api.secret');
        $this->timeout = config('external_api.timeout', 30);
        $this->retryTimes = config('external_api.retry.times', 3);
        $this->retrySleep = config('external_api.retry.sleep', 1000);
    }

    /**
     * Fetch data from an external API endpoint.
     *
     * @param string $entity Entity key (e.g. 'sekolah', 'siswa')
     * @return array The decoded data array from the API response
     * @throws \Exception If the request fails after all retries
     */
    public function fetch(string $entity): array
    {
        $endpoint = config("external_api.endpoints.{$entity}");

        if (!$endpoint) {
            throw new \InvalidArgumentException("Endpoint tidak ditemukan untuk entity: {$entity}");
        }

        $url = "{$this->baseUrl}/{$endpoint}/{$this->secret}";



        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep, function (\Exception $exception, $request) {
                    // Retry on connection errors and 5xx server errors
                    if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                        return true;
                    }
                    if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                        return $exception->response->serverError();
                    }
                    return false;
                })
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                Log::error("External API: HTTP {$response->status()} for {$entity}", [
                    'body' => substr($response->body(), 0, 500),
                ]);
                throw new \Exception("External API error: HTTP {$response->status()} untuk endpoint {$entity}");
            }

            $body = $response->json();

            // Handle various response formats
            $data = $this->extractData($body);



            return $data;
        } catch (\Exception $e) {
            Log::error("External API: Failed to fetch {$entity}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract data array from various API response formats.
     */
    protected function extractData(mixed $body): array
    {
        if (is_array($body)) {
            // If response has a 'data' key, use it
            if (isset($body['data']) && is_array($body['data'])) {
                return $body['data'];
            }
            // If the response is a sequential array, use it directly
            if (array_is_list($body)) {
                return $body;
            }
            // Wrap single object in array
            return [$body];
        }

        return [];
    }

    /**
     * Mask the secret in URL for logging purposes.
     */
    protected function maskUrl(string $url): string
    {
        if ($this->secret) {
            return str_replace($this->secret, '***HIDDEN***', $url);
        }
        return $url;
    }

    /**
     * Test connectivity to the external API.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(): array
    {
        try {
            $data = $this->fetch('sekolah');
            return [
                'success' => true,
                'message' => 'Koneksi berhasil. Data sekolah ditemukan: ' . count($data),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ];
        }
    }
}
