<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductType;
class ProductsController extends Controller
{
    public function index()
    {
        $products = Products::with('product_type', 'added_by')->get();
        return response()->json($products);
       
    }

  public function productTypes()
    {
        $productTypes = ProductType::all();
        return response()->json($productTypes);
    }

    public function store(Request $request)
    {
        // Directly get the data from the request
        $data = $request->all();
    
        // Create a new user with the data (ensure that the fields are mass assignable in the model)
        $data['addedBy'] = auth()->user()->id; // Assuming the user is authenticated and you want to set the addedBy field to the current user's ID
        $products = Products::create($data);

       $products->load(['product_type', 'added_by']);
        return response()->json([
            'productId' => $products->productId,
            'productName' => $products->productName,
            'productType' => $products->product_type->typeName,
            'cost' => $products->cost,
            'addedBy' => $products->added_by->firstName . ' ' . $products->added_by->lastName,
        ], 201); // HTTP status code 201: Created
    }



    public function show($productId)
    {
        $product = Products::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function edit($productId)
    {
        $product = Products::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function update(Request $request, $productId)
    {
        $product = Products::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($productId)
    {
        $product = Products::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
    
}
