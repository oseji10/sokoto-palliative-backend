<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollees;
use App\Models\User; 
use App\Models\EnrolleeType;
class EnrolleesController extends Controller
{
    public function index()
    {
        $enrollees = Enrollees::with('enrolled_by', 'enrollee_type', 'lga_info')->get();
        return response()->json($enrollees);

    }

    public function enrolleeTypes()
    {
        $enrolleeTypes = EnrolleeType::all();
        return response()->json($enrolleeTypes);
    }

   public function store(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'otherNames' => 'nullable|string|max:255',
        'phoneNumber' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255|unique:enrollees,email',
        'enrolleeType' => 'required|integer|exists:enrollee_type,typeId',
    ]);

    // Get the authenticated staff
    $user = auth()->user();

    // Ensure the user is authenticated and has an LGA
    if (!$user || !isset($user->staff->lga)) {
        return response()->json(['message' => 'Authenticated staff or LGA not found'], 403);
    }

    // Prepare the data for creation
    $data = array_merge($validatedData, [
        'enrolledBy' => $user->id,
        'lga' => $user->staff->lga, // Assign the staff's LGA to the enrollee
    ]);

    // Create the enrollee
    $enrollee = Enrollees::create($data);
    // $enrollee->load(['enrollee_type', 'enrolled_by', 'lga_info']);

    $enrollee->load(['enrollee_type', 'enrolled_by', 'lga_info']);
    return response()->json([
        'enrolleeId' => $enrollee->enrolleeId,
        'firstName' => $enrollee->firstName,
        'lastName' => $enrollee->lastName,
        'otherNames' => $enrollee->otherNames,
        'phoneNumber' => $enrollee->phoneNumber,
        'email' => $enrollee->email,
        'enrolleeType' => $enrollee->enrollee_type->typeName,
        'enrolledBy' => $enrollee->enrolled_by->firstName . ' ' . $enrollee->enrolled_by->lastName,
    ], 201);
}
}
