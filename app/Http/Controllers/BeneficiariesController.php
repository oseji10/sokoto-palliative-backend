<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiary;
use App\Models\User; 
use App\Models\BeneficiaryType;
use App\Models\BeneficiaryImage;
class BeneficiariesController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::with('enrolled_by', 'beneficiary_type', 'lga_info', 'cadre_info', 'ministry_info', 'beneficiary_image')
        ->orderBy('created_at', 'desc')
        ->get();
        return response()->json($beneficiaries);

    }

    public function getOnboarderBeneficiaries()
    {
        $user = auth()->user();
        // return $user->staff->lga;
        $beneficiaries = Beneficiary::with('enrolled_by', 'beneficiary_type', 'lga_info', 'cadre_info', 'ministry_info', 'beneficiary_image')
        ->where('lga', $user->staff->lga)
        ->orderBy('created_at', 'desc')
        ->get();
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
        'ministry' => 'nullable|integer|exists:ministries,ministryId',
        'cadre' => 'nullable|integer|exists:cadres,cadreId',
        'employeeId' => 'nullable|string|max:255',
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust validation rules as needed

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

    $imagePath = $request->file('image')->store('beneficiary_images', 'public');
        
        // Assuming you have a ProductImage model to handle product images
        $beneficiary->beneficiary_image()->create([
            'imagePath' => $imagePath,
            'beneficiaryId' => $beneficiary->beneficiaryId, 
        ]);
        
    $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info', 'cadre_info', 'ministry_info', 'beneficiary_image']);
    return response()->json([
        'beneficiaryId' => $beneficiary->beneficiaryId,
        'employeeId' => $beneficiary->employeeId,
        'firstName' => $beneficiary->firstName,
        'lastName' => $beneficiary->lastName,
        'otherNames' => $beneficiary->otherNames,
        'phoneNumber' => $beneficiary->phoneNumber,
        'email' => $beneficiary->email,
        'lga' => $beneficiary->lga_info ? $beneficiary->lga_info->lgaName : null,
        'ministry' => $beneficiary->ministry_info ? $beneficiary->ministry_info->ministryName : null,
        'cadre' => $beneficiary->cadre_info ? $beneficiary->cadre_info->cadreName : null,
        'beneficiaryType' => $beneficiary->beneficiary_type->typeName,
        'enrolledBy' => $beneficiary->enrolled_by->firstName . ' ' . $beneficiary->enrolled_by->lastName,
    ], 201);
}

// public function show($beneficiaryId)
// {
//     $beneficiary = Beneficiary::find($beneficiaryId);
//     if (!$beneficiary) {
//         return response()->json(['message' => 'Beneficiary not found'], 404);
//     }
//     $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info']);
//     return response()->json($beneficiary);

//     $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info']);
// }



public function update(Request $request, $beneficiaryId)
{
    $beneficiary = Beneficiary::find($beneficiaryId);
    if (!$beneficiary) {
        return response()->json(['message' => 'Beneficiary not found'], 404);
    }

    // Validate the request data
    $validatedData = $request->validate([
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'otherNames' => 'nullable|string|max:255',
        'phoneNumber' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255|unique:beneficiaries,email,' . $beneficiary->beneficiaryId,
        'beneficiaryType' => 'required|integer|exists:beneficiary_type,typeId',
        'ministry' => 'nullable|integer|exists:ministries,ministryId',
        'cadre' => 'nullable|integer|exists:cadres,cadreId',
        'employeeId' => 'nullable|string|max:255',
    ]);

    // Update the beneficiary
    $beneficiary->update($validatedData);
    $beneficiary->load(['beneficiary_type', 'enrolled_by', 'lga_info', 'cadre_info', 'ministry_info']);

    return response()->json([
        'message' => "Beneficiary successfully updated",
        'beneficiaryId' => $beneficiary->beneficiaryId,
        'employeeId' => $beneficiary->employeeId,
        'firstName' => $beneficiary->firstName,
        'lastName' => $beneficiary->lastName,
        'otherNames' => $beneficiary->otherNames,
        'phoneNumber' => $beneficiary->phoneNumber,
        'email' => $beneficiary->email,
        'lga' => $beneficiary->lga_info ? $beneficiary->lga_info->lgaName : null,
        'ministry' => $beneficiary->ministry_info ? $beneficiary->ministry_info->ministryName : null,
        'cadre' => $beneficiary->cadre_info ? $beneficiary->cadre_info->cadreName : null,
        'beneficiaryType' => $beneficiary->beneficiary_type->typeName,
        'enrolledBy' => $beneficiary->enrolled_by->firstName . ' ' . $beneficiary->enrolled_by->lastName,
    ], 200);
}

public function destroy($beneficiaryId)
{
    $beneficiary = Beneficiary::find($beneficiaryId);
    if (!$beneficiary) {
        return response()->json(['message' => 'Beneficiary not found'], 404);
    }

    // Soft delete the beneficiary
    $beneficiary->delete();

    return response()->json(['message' => 'Beneficiary deleted successfully'], 200);
}
}
