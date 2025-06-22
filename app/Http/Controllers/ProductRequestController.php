<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductRequest;
class ProductRequestController extends Controller
{
    public function index()
    {
        $productrequests = ProductRequest::all();
        return response()->json($productrequests);
    }
    public function show($productrequestId)
    {
        $productrequest = ProductRequest::find($productrequestId);
        if (!$productrequest) {
            return response()->json(['message' => 'ProductRequest not found'], 404);
        }
        return response()->json($productrequest);
    }

    public function store(Request $request)
    {
        // Directly get the data from the request
        $data = $request->all();
    
        // Create a new user with the data (ensure that the fields are mass assignable in the model)
        $productrequests = ProductRequest::create($data);
        
        // Return a response, typically JSON
        return response()->json([
            'message' => 'ProductRequest created successfully',
            'productrequestId' => $productrequests->productrequestId,
            'productrequestName' => $productrequests->productrequestName], 201); // HTTP status code 201: Created
    }

    public function update(Request $request, $productrequestId)
    {
        $productrequest = ProductRequest::find($productrequestId);
        if (!$productrequest) {
            return response()->json(['message' => 'ProductRequest not found'], 404);
        }

        $data = $request->all();
        $productrequest->update($data);

        return response()->json([
            'message' => 'ProductRequest updated successfully',
            'productrequestId' => $productrequest->productrequestId,
            'productrequestName' => $productrequest->productrequestName], 201); // HTTP status code 201: Created

    }
    
    public function destroy($productrequestId)
    {
        $productrequest = ProductRequest::find($productrequestId);
        if (!$productrequest) {
            return response()->json(['message' => 'ProductRequest not found'], 404);
        }

        $productrequest->delete();
        return response()->json(['message' => 'ProductRequest deleted successfully']);
    }
    
}
