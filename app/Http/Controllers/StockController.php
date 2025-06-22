<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Products;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
     public function index()
    {
        $stocks = Stock::with('product', 'lga_info')->get();
        return response()->json($stocks);
       
    }


     public function availableStock()
    {
        $user = auth()->user();
        $stocks = Stock::with('product', 'lga_info')
        ->where('lgaId', $user->staff->lga)
        ->get();
        return response()->json($stocks);
       
    }


     public function store(Request $request)
    {
       $validated = $request->validate([
            'stockName' => 'required|string|max:255',
        ]);

        $stocks = Stock::create($validated);
        return response()->json($stocks, 201); // HTTP status code 201: Created

    }

   public function edit(Request $request, $stockId)
{
    $validated = $request->validate([
        'stockName' => 'required|string|max:255',
    ]);

    $stock = Stock::where('stockId', $stockId)->first();
    if (!$stock) {
        return response()->json(['message' => 'Stock type not found'], 404);
    }

    $stock->update($validated);
    
    return response()->json([
        'stockId' => $stock->stockId,
        'stockName' => $stock->stockName
    ], 200);
}

    public function destroy($stockId)
    {
        $stock = Stock::where('stockId', $stockId)->first();
        if (!$stock) {
            return response()->json(['message' => 'Stock type not found'], 404);
        }

        $stock->delete();
        return response()->json(['message' => 'Stock type deleted successfully'], 200);
    }
    // public function show($stockId)
    // {
    //     $stock = Stock::where('stockId', $stockId)->first();
    //     if (!$stock) {
    //         return response()->json(['message' => 'Stock type not found'], 404);
    //     }
    //     return response()->json($stock);
    // }
   
}
