<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transactions;
use App\Models\TransactionProducts;
use App\Models\PendingTransactions;
use App\Models\Products;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class TransactionsController extends Controller
{
    public function index()
    {
        $transactions = Transactions::with('transaction_products.products', 'beneficiary', 'seller')
        ->orderBy('created_at', 'desc')
        ->get();
        return response()->json($transactions);
    }
    public function show($transactionId)
    {
        $transaction = Transactions::find($transactionId);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json($transaction);
    }

    

   public function initiate(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'beneficiaryId' => 'required|exists:beneficiaries,beneficiaryId',
            'products' => 'required|array|min:1',
            'products.*.productId' => 'required|exists:products,productId',
            'products.*.quantity' => 'required|integer|min:1',
            'paymentMethod' => 'required|in:outright,loan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Calculate total cost and validate stock
            $products = $request->products;
            $totalCost = 0;

            foreach ($products as $product) {
                $productId = $product['productId'];
                $quantity = $product['quantity'];

                $productModel = Products::find($productId);
                if (!$productModel) {
                    throw new \Exception("Product not found for product ID {$productId}");
                }

                $stock = Stock::where('productId', $productId)->first();
                if (!$stock) {
                    throw new \Exception("Stock not found for product ID {$productId}");
                }

                $availableStock = $stock->quantityReceived - ($stock->quantitySold ?? 0);
                if ($quantity > $availableStock) {
                    throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$availableStock}, Requested: {$quantity}");
                }

                $totalCost += $productModel->cost * $quantity;
            }

            // Generate a random transaction ID
            $transactionId = Str::random(12);

            // Call Moniepoint API for outright payments
            if ($request->paymentMethod === 'outright') {
    $totalCostInKobo = $totalCost;

    $moniepointResponse = Http::withHeaders([
        'Authorization' => 'Bearer mptp_a72e62d6220b4c279f05f0d90c71f79b_cce5ff',
        'Cookie' => '__cf_bm=your_cookie_here'
    ])->post('https://api.pos.moniepoint.com/v1/transactions', [
        'terminalSerial' => 'P260302358597',
        'amount' => $totalCostInKobo,
        'merchantReference' => $transactionId,
        'transactionType' => 'PURCHASE',
        'paymentMethod' => 'CARD_PURCHASE'
    ]);

    // ✅ Check if the request was successful
    if ($moniepointResponse->status() === 202) {
        return response()->json([
            'status' => 'success',
            'message' => 'Payment request accepted by Moniepoint.',
            'moniepoint_status' => $moniepointResponse->status(),
            'moniepoint_description' => 'Accepted'
        ], 202);
    } else {
        // Log full Moniepoint response for debugging
        \Log::error('Moniepoint failed', [
            'status' => $moniepointResponse->status(),
            'body' => $moniepointResponse->body()
        ]);

        // Decode response body if it's JSON
        $errorMessage = $moniepointResponse->json('error') ?? 'Payment request failed.';

        return response()->json([
            'status' => 'error',
            'message' => $errorMessage,
            'moniepoint_status' => $moniepointResponse->status(),
        ], $moniepointResponse->status());
    }



                
                // Check if Moniepoint payment was successful
                // if ($moniepointResponse->failed() || !isset($moniepointResponse['code']) || $moniepointResponse['code'] !== '202') {
                //     return response()->json([
                //         'message' => 'Payment processing failed',
                //         'error' => $moniepointResponse['message'] ?? 'Moniepoint API error'
                //     ], 400);
                // }
            }

            // Store in PendingTransactions
            $pendingTransaction = PendingTransactions::create([
                'transactionId' => $transactionId,
                'beneficiaryId' => $request->beneficiaryId,
                'paymentMethod' => $request->paymentMethod,
                'products' => json_encode($products),
                'totalCost' => $totalCost,
                'status' => $request->paymentMethod === 'outright' ? 'completed' : 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([$moniepointResponse],200);

            // return response()->json([
            //     'transactionId' => $transactionId,
            //     'status' => $pendingTransaction->status
            // ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to initiate transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirm(Request $request, string $transactionId): JsonResponse
    {
        try {
            // Find pending transaction
            $pendingTransaction = PendingTransactions::where('transactionId', $transactionId)->first();
            if (!$pendingTransaction) {
                return response()->json([
                    'message' => 'Pending transaction not found'
                ], 404);
            }

            // If already processed, return existing transaction
            $existingTransaction = Transactions::where('transactionId', $transactionId)->first();
            if ($existingTransaction) {
                return response()->json($existingTransaction, 200);
            }

            // For outright, ensure payment was completed
            if ($pendingTransaction->paymentMethod === 'outright' && $pendingTransaction->status !== 'completed') {
                return response()->json([
                    'message' => 'Payment not confirmed',
                    'status' => 'failed'
                ], 400);
            }

            // Begin a database transaction
            return DB::transaction(function () use ($pendingTransaction, $transactionId) {
                // Get authenticated user
                $user = auth()->user();
                if (!$user || !$user->staff) {
                    throw new \Exception('Authenticated user or staff data not found');
                }

                // Create the transaction
                $transaction = Transactions::create([
                    'transactionId' => $transactionId,
                    'beneficiary' => $pendingTransaction->beneficiaryId,
                    'paymentMethod' => $pendingTransaction->paymentMethod,
                    'lga' => $user->staff->lga,
                    'soldBy' => $user->id,
                    'status' => $pendingTransaction->status,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Process products
                $products = json_decode($pendingTransaction->products, true);
                $transactionProducts = [];

                foreach ($products as $product) {
                    $productId = $product['productId'];
                    $quantity = $product['quantity'];

                    // Fetch product details
                    $productModel = Products::findOrFail($productId);

                    // Check stock availability
                    $stock = Stock::where('productId', $productId)->firstOrFail();
                    $availableStock = $stock->quantityReceived - ($stock->quantitySold ?? 0);

                    if ($quantity > $availableStock) {
                        throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$availableStock}, Requested: {$quantity}");
                    }

                    // Create transaction product entry
                    $transactionProducts[] = [
                        'transactionId' => $transactionId,
                        'productId' => $productId,
                        'quantitySold' => $quantity,
                        'cost' => $productModel->cost,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    // Update stock
                    $stock->increment('quantitySold', $quantity);
                }

                // Insert transaction products
                TransactionProducts::insert($transactionProducts);

                // Delete pending transaction
                $pendingTransaction->delete();

                // Fetch the transaction with related data
                $transaction = Transactions::with(['beneficiary', 'transaction_products.products'])
                    ->where('transactionId', $transactionId)
                    ->firstOrFail();

                // Format response
                $response = [
                    'id' => $transaction->id,
                    'beneficiary' => $transaction->beneficiary,
                    'transactionId' => $transaction->transactionId,
                    'lga' => $transaction->lga,
                    'soldBy' => $transaction->soldBy,
                    'paymentMethod' => $transaction->paymentMethod,
                    'status' => $transaction->status,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'updated_at' => $transaction->updated_at->toIso8601String(),
                    'transaction_products' => $transaction->transaction_products->map(function ($transactionProduct) {
                        return [
                            'id' => $transactionProduct->id,
                            'transactionId' => $transactionProduct->transactionId,
                            'productId' => $transactionProduct->productId,
                            'quantitySold' => (string) $transactionProduct->quantitySold,
                            'cost' => (string) $transactionProduct->cost,
                            'created_at' => $transactionProduct->created_at ? $transactionProduct->created_at->toIso8601String() : null,
                            'updated_at' => $transactionProduct->updated_at ? $transactionProduct->updated_at->toIso8601String() : null,
                            'products' => [
                                'productId' => $transactionProduct->products->productId,
                                'productName' => $transactionProduct->products->productName ?? 'Unknown Product',
                                'productType' => $transactionProduct->products->productType,
                                'cost' => (string) $transactionProduct->products->cost,
                                'addedBy' => $transactionProduct->products->addedBy,
                                'status' => $transactionProduct->products->status,
                                'created_at' => $transactionProduct->products->created_at->toIso8601String(),
                                'updated_at' => $transactionProduct->products->updated_at->toIso8601String(),
                            ]
                        ];
                    })->toArray(),
                ];

                return response()->json($response, 200);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm transaction',
                'error' => $e->getMessage(),
                'status' => 'failed'
            ], 500);
        }
    }
    
    public function update(Request $request, $transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $data = $request->all();
        $transaction->update($data);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transactionId' => $transaction->transactionId,
            'transactionName' => $transaction->transactionName], 201); // HTTP status code 201: Created

    }
    
    public function destroy($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully']);
    }
    
}
