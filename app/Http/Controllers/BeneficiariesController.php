<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiary;
use App\Models\User; 
use App\Models\BeneficiaryType;
class BeneficiariesController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::with('enrolled_by', 'beneficiary_type', 'lga_info')->get();
        return response()->json($beneficiaries);

    }

    public function beneficiaryTypes()
    {
        $beneficiaryTypes = BeneficiaryType::all();
        return response()->json($beneficiaryTypes);
    }

   public function store(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'otherNames' => 'nullable|string|max:255',
        'phoneNumber' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255|unique:beneficiaries,email',
        'beneficiaryType' => 'required|integer|exists:beneficiary_type,typeId',
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
        'lga' => $user->staff->lga, // Assign the staff's LGA to the beneficiary
    ]);

    // Create the beneficiary
    $beneficiary = Beneficiary::create($data);
    // $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info']);

    $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info']);
    return response()->json([
        'beneficiaryId' => $beneficiary->beneficiaryId,
        'firstName' => $beneficiary->firstName,
        'lastName' => $beneficiary->lastName,
        'otherNames' => $beneficiary->otherNames,
        'phoneNumber' => $beneficiary->phoneNumber,
        'email' => $beneficiary->email,
        'beneficiaryType' => $beneficiary->beneficiary_type->typeName,
        'enrolledBy' => $beneficiary->enrolled_by->firstName . ' ' . $beneficiary->enrolled_by->lastName,
    ], 201);
}
}
