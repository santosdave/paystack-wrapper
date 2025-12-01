<?php

namespace SantosDave\Paystack\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use SantosDave\Paystack\Exceptions\PaystackException;
use SantosDave\Paystack\Exceptions\AuthenticationException;
use SantosDave\Paystack\Exceptions\ValidationException;

class Client
{
    protected GuzzleClient $client;
    protected string $baseUrl;
    protected string $secretKey;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = array_merge(config('paystack', []), $config);
        $this->baseUrl = $this->config['base_url'];
        $this->secretKey = $this->config['secret_key'];

        $this->validateConfig();
        $this->initializeClient();
    }

    protected function validateConfig(): void
    {
        if (empty($this->secretKey)) {
            throw new AuthenticationException('Paystack secret key is not set. Please set PAYSTACK_SECRET_KEY in your .env file.');
        }

        if (empty($this->baseUrl)) {
            throw new PaystackException('Paystack base URL is not set.');
        }

        // Ensure SSL verification is enabled in production
        if (app()->environment('production') && !$this->config['http']['verify']) {
            throw new PaystackException('SSL verification must be enabled in production environment.');
        }
    }

    protected function initializeClient(): void
    {
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->config['timeout'] ?? 30,
            'connect_timeout' => $this->config['http']['connect_timeout'] ?? 10,
            'verify' => $this->config['http']['verify'] ?? true,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache',
            ],
        ]);
    }

    /**
     * Send a GET request.
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $params,
        ]);
    }

    /**
     * Send a POST request.
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Send a PUT request.
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Send a DELETE request.
     */
    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Send an HTTP request to Paystack API.
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $this->logRequest($method, $endpoint, $options);

            $response = $this->client->request($method, $endpoint, $options);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $this->logResponse($data);

            if (!is_array($data)) {
                throw new PaystackException('Invalid JSON response from Paystack API.');
            }

            if (isset($data['status']) && $data['status'] === false) {
                $this->handleErrorResponse($data);
            }

            return $data;
        } catch (RequestException $e) {
            $this->handleRequestException($e);
            return []; // Ensure an array is always returned
        } catch (GuzzleException $e) {
            throw new PaystackException(
                'HTTP request failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Handle error responses from Paystack.
     */
    protected function handleErrorResponse(array $data): void
    {
        $message = $data['message'] ?? 'Unknown error occurred';
        $code = $data['code'] ?? 0;

        // Check for specific error types
        if (str_contains(strtolower($message), 'authorization')) {
            throw new AuthenticationException($message, $code);
        }

        if (
            str_contains(strtolower($message), 'validation') ||
            str_contains(strtolower($message), 'invalid')
        ) {
            throw new ValidationException($message, $code, $data['errors'] ?? []);
        }

        throw new PaystackException($message, $code);
    }

    /**
     * Handle Guzzle request exceptions.
     */
    protected function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();

        if ($response) {
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            $message = is_array($data) && isset($data['message'])
                ? $data['message']
                : $response->getReasonPhrase();

            switch ($statusCode) {
                case 401:
                    throw new AuthenticationException(
                        'Authentication failed: ' . $message,
                        $statusCode,
                        $e
                    );

                case 422:
                    throw new ValidationException(
                        'Validation failed: ' . $message,
                        $statusCode,
                        is_array($data) ? ($data['errors'] ?? []) : [],
                        $e
                    );

                case 429:
                    throw new PaystackException(
                        'Rate limit exceeded. Please try again later.',
                        $statusCode,
                        $e
                    );

                default:
                    throw new PaystackException(
                        'API request failed: ' . $message,
                        $statusCode,
                        $e
                    );
            }
        }

        throw new PaystackException(
            'Request failed: ' . $e->getMessage(),
            0,
            $e
        );
    }

    /**
     * Log API request if logging is enabled.
     */
    protected function logRequest(string $method, string $endpoint, array $options): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            Log::channel($this->config['logging']['channel'] ?? 'stack')->info('Paystack API Request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'options' => $this->sanitizeLogData($options),
            ]);
        }
    }

    /**
     * Log API response if logging is enabled.
     */
    protected function logResponse(array $data): void
    {
        if ($this->config['logging']['enabled'] ?? false) {
            Log::channel($this->config['logging']['channel'] ?? 'stack')->info('Paystack API Response', [
                'data' => $this->sanitizeLogData($data),
            ]);
        }
    }

    /**
     * Sanitize sensitive data from logs.
     */
    protected function sanitizeLogData(array $data): array
    {
        $sensitive = ['authorization', 'secret', 'password', 'token', 'cvv', 'pin'];

        array_walk_recursive($data, function (&$value, $key) use ($sensitive) {
            if (is_string($key) && in_array(strtolower($key), $sensitive)) {
                $value = '***REDACTED***';
            }
        });

        return $data;
    }

    /**
     * Get the HTTP client instance.
     */
    public function getClient(): GuzzleClient
    {
        return $this->client;
    }
}