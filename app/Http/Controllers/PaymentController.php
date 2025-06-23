<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Requests\ProcessPaymentRequest;
use App\Services\MoniepointPosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\User as Authenticatable;

class PaymentController extends BaseController
{
    public function __construct(private readonly MoniepointPosService $moniepointService)
    {
    }

    /**
     * Push payment request to POS terminal
     *
     * @param ProcessPaymentRequest $request
     * @return JsonResponse
     */
    public function pushPayment(ProcessPaymentRequest|Request $request)
    {
        try {
            $data = $request->validated();
            $ref = $this->moniepointService->generateReference();
            $amount = (int) round($data['amount'] * 100);
            $user = Auth::user();
            $lga = $user?->staff?->lga_info?->lgaName;
            $terminal = $this->moniepointService->getTerminalSerial($lga);

            $txn = new Transaction([
                'reference' => $ref,
                'amount' => $amount,
                'terminal_serial' => $terminal,
                'payment_method' => $data['payment_method'] ?? 'ANY',
                'status' => Transaction::STATUS_PENDING,
                'user_id' => $user?->id,
            ]);
            $txn->save();

            $result = $this->moniepointService->pushPaymentRequest([
                'terminal_serial' => $terminal,
                'amount' => $amount,
                'merchant_reference' => $ref,
            ]);

            if (!$result['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'],
                    'data' => $result['data'] ?? null
                ], $result['status_code'] ?? 400);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment request sent',
                'data' => [
                    'reference' => $ref,
                    'amount' => $data['amount'],
                    'status' => $txn->status,
                    'transaction_id' => $txn->id,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Payment error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Payment processing failed',
            ], 500);
        }
    }

    /**
     * Get transaction status
     *
     * @param string $reference
     * @return JsonResponse
     */
    public function getTransactionStatus(string $reference)
    {
        try {
            if (!$txn = Transaction::where('reference', $reference)->first()) {
                return response()->json(['status' => false, 'message' => 'Transaction not found'], 404);
            }

            $result = $this->moniepointService->getTransactionStatus($reference);
            if (!empty($result['data'])) {
                $this->updateTransactionFromTerminalData($txn, $result['data']);
                $txn->refresh();
            }

            if (empty($result['status'])) {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Failed to get status'
                ], $result['status_code'] ?? 400);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'reference' => $txn->reference,
                    'amount' => $txn->amount / 100,
                    'status' => $txn->status,
                    'paid_at' => $txn->paid_at,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Status check failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Status check failed'], 500);
        }
    }

    /**
     * Update local transaction from terminal data
     *
     * @param Transaction $transaction
     * @param array $terminalData
     * @return void
     */
/**
     * Update transaction status based on terminal response
     */
    /**
     * Update transaction status based on terminal response
     */
    protected function updateTransactionFromTerminalData(Transaction $txn, array $data): void
    {
        $status = match($data['processingStatus'] ?? '') {
            'COMPLETED' => Transaction::STATUS_COMPLETED,
            'CANCELLED', 'FAILED' => Transaction::STATUS_CANCELLED,
            default => $txn->status,
        };

        $txn->update([
            'status' => $status,
            'response' => array_merge($txn->response ?? [], $data),
            'paid_at' => $status === Transaction::STATUS_COMPLETED && !$txn->paid_at ? now() : $txn->paid_at,
        ]);
    }
}
