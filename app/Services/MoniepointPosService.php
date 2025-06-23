<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;

class MoniepointPosService
{
    protected string $baseUrl;
    protected string $bearerToken;
    protected int $timeout = 30;
    protected int $maxRetries = 3;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('moniepoint.base_url'), '/');
        $this->bearerToken = config('moniepoint.bearer_token');
    }

    public function pushPaymentRequest(array $data): array
    {
        $url = "{$this->baseUrl}/v1/transactions";
        
        try {
            $payload = [
                'terminalSerial' => $data['terminal_serial'],
                'amount' => (int) $data['amount'],
                'merchantReference' => $data['merchant_reference'],
                'transactionType' => 'PURCHASE',
                'paymentMethod' => $data['payment_method'] ?? 'CARD_PURCHASE',
            ];
            
            $response = Http::withToken($this->bearerToken)
                ->acceptJson()
                ->timeout($this->timeout)
                ->retry($this->maxRetries, 100, fn($e) => $e instanceof RequestException && 
                    ($e->response?->serverError() || $e->response === null))
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Payment request sent',
                    'data' => $response->json(),
                ];
            }

            return [
                'status' => false,
                'message' => @$response->json()['message'] ?? 'Failed to get status',
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];

        } catch (RequestException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => $e->response?->status() ?? 500,
            ];
        } catch (\Exception $e) {
            Log::error('Payment error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Payment processing failed',
                'status_code' => 500,
            ];
        }
    }

    public function getTransactionStatus(string $merchantReference): array
    {
        $url = "{$this->baseUrl}/v1/transactions/merchants/" . urlencode($merchantReference);
        
        try {
            $response = Http::withToken($this->bearerToken)
                ->acceptJson()
                ->timeout($this->timeout)
                ->retry($this->maxRetries, 100, fn($e) => $e instanceof RequestException && 
                    ($e->response?->serverError() || $e->response === null))
                ->get($url);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Transaction status retrieved',
                    'data' => $response->json(),
                ];
            }

            return [
                'status' => false,
                'message' => @$response->json()['message'] ?? 'Failed to get status',
                'data' => $response->json(),
                'status_code' => $response->status(),
            ];

        } catch (RequestException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => $e->response?->status() ?? 500,
            ];
        } catch (\Exception $e) {
            Log::error('Transaction status error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Transaction status retrieval failed',
                'status_code' => 500,
            ];
        }
    }

    public function generateReference(): string
    {
        return 'TXN' . time() . rand(1000, 9999);
    }
    
    public function getTerminalSerial(?string $lga): string
    {
        return match($lga) {
            'Sokoto North' => '',
            default => '',
        };
    }
}
