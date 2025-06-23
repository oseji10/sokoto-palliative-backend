<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transactions;
use App\Models\TransactionProducts;
use App\Models\Products;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
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

    

    public function store(Request $request): JsonResponse
{
    // Validate the incoming request
    $validator = Validator::make($request->all(), [
        'beneficiaryId' => 'required|exists:beneficiaries,beneficiaryId',
        'products' => 'required|array|min:1',
        'products.*.productId' => 'required|exists:products,productId',
        'products.*.quantity' => 'required|integer|min:1',
        'paymentMethod' => 'required|in:outright,loan', // Added validation for paymentMethod
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
            // Generate a random transaction ID
            $transactionId = Str::random(12);

            // Get authenticated user
            $user = auth()->user();
            if (!$user || !$user->staff) {
                throw new \Exception('Authenticated user or staff data not found');
            }

            // Create the transaction
            $transaction = Transactions::create([
                'transactionId' => $transactionId,
                'beneficiary' => $request->beneficiaryId,
                'paymentMethod' => $request->paymentMethod,
                'lga' => $user->staff->lga,
                'soldBy' => $user->id,
                'status' => 'pending',
                
            ]);

            // Process each product
            $products = $request->products;
            $transactionProducts = [];

            foreach ($products as $product) {
                $productId = $product['productId'];
                $quantity = $product['quantity'];

                // Fetch product details (including price)
                $productModel = Products::find($productId);
                if (!$productModel) {
                    throw new \Exception("Product not found for product ID {$productId}");
                }

                // Check stock availability
                $stock = Stock::where('productId', $productId)->first();
                if (!$stock) {
                    throw new \Exception("Stock not found for product ID {$productId}");
                }

                $availableStock = $stock->quantityReceived - (
                    ($stock->quantitySold ?? 0)
                );

                if ($quantity > $availableStock) {
                    throw new \Exception("Insufficient stock for product ID {$productId}. Available: {$availableStock}, Requested: {$quantity}");
                }

                // Create transaction product entry
                $transactionProducts[] = [
                    'transactionId' => $transactionId,
                    'productId' => $productId,
                    'quantitySold' => $quantity,
                    'cost' => $productModel->cost, // Store product price
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                // Update stock (increment quantity_sold)
                $stock->increment('quantitySold', $quantity);
            }

            // Insert transaction products
            TransactionProducts::insert($transactionProducts);

            // Fetch the transaction with related data
            $transaction = Transactions::with(['beneficiary', 'products.product', 'transaction_products.products'])
                ->where('transactionId', $transactionId)
                ->firstOrFail();

            // Format response
      $response = [
    'transactionId' => $transaction->transactionId,
    'beneficiary' => $transaction->beneficiary, // You probably want full data, not just ID
    'status' => $transaction->status,
    'paymentMethod' => $transaction->paymentMethod,
    
    // Map over all transaction_products
    'products' => $transaction->transaction_products->map(function ($transactionProduct) {
        return [
            'productId' => $transactionProduct->productId,
            'productName' => $transactionProduct->product->productName ?? 'Unknown Product',
            'quantitySold' => $transactionProduct->quantitySold,
            'cost' => $transactionProduct->cost,
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
