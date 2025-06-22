<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transactions;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
class TransactionsController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }
    public function show($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json($transaction);
    }

    

     
    public function store(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'beneficiaryId' => 'required|exists:beneficiaries,beneficiaryId',
            'products' => 'required|array|min:1',
            'products.*.productId' => 'required|exists:products,productId',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Begin a database transaction
            return DB::transaction(function () use ($request) {
                // Generate a random transaction ID (UUID)
                $transactionId = Str::uuid()->toString();

                // Create the transaction
                $transaction = Transactions::create([
                    'transactionId' => $transactionId,
                    'beneficiaryId' => $request->beneficiaryId,
                    // 'request_date' => Carbon::now(),
                    // 'status' => 'pending',
                ]);

                // Process each product
                $products = $request->products;
                $transactionProducts = [];

                foreach ($products as $product) {
                    $productId = $product['productId'];
                    $quantity = $product['quantity'];

                    // Check stock availability
                    $stock = Stock::where('productId', $productId)->first();
                    if (!$stock) {
                        throw new \Exception("Stock not found for product ID {$productId}");
                    }

                    $availableStock = $stock->quantity_received - (
                        ($stock->quantitySold ?? 0) 
                        // ($stock->quantity_transferred ?? 0) +
                        // ($stock->quantity_expired ?? 0) +
                        // ($stock->quantity_damaged ?? 0)
                    );

                    if ($quantity > $availableStock) {
                        throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$availableStock}, Requested: {$quantity}");
                    }

                    // Create transaction product entry
                    $transactionProducts[] = [
                        'transactionId' => $transactionId,
                        'productId' => $productId,
                        'quantitySold' => $quantity,
                        // 'quantity_dispatched' => 0,
                        // 'quantity_received' => 0,
                        // 'created_at' => Carbon::now(),
                        // 'updated_at' => Carbon::now(),
                    ];

                    // Update stock (increment quantity_sold)
                    $stock->increment('quantitySold', $quantity);
                }

                // Insert transaction products
                TransactionProduct::insert($transactionProducts);

                // Fetch the transaction with related data
                $transaction = Transactions::with(['beneficiary', 'products.product'])
                    ->where('transaction_id', $transactionId)
                    ->first();

                // Format response
                $response = [
                    'transactionId' => $transaction->transactionId,
                    'beneficiaryId' => $transaction->beneficiaryId,
                    // 'requestDate' => $transaction->request_date->toIso8601String(),
                    // 'status' => $transaction->status,
                    'products' => $transaction->products->map(function ($transactionProduct) {
                        return [
                            'productId' => $transactionProduct->productId,
                            'productName' => $transactionProduct->product->productName ?? 'Unknown Product',
                            // 'quantityRequested' => $transactionProduct->quantity_requested,
                            // 'quantityDispatched' => $transactionProduct->quantity_dispatched,
                            // 'quantityReceived' => $transactionProduct->quantity_received,
                        ];
                    })->toArray(),
                ];

                return response()->json($response, 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
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
