<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HospitalController extends Controller
{
    public function index()
    {
        // This method should return a list of hospitals
        $hospitals = Hospital::with(['contact_person', 'hospital_location'])->get();
        if ($hospitals->isEmpty()) {
            return response()->json(['message' => 'No hospitals found'], 404);
        }
        return response()->json($hospitals);
    }


     public function store(Request $request)
    {
       $validated = $request->validate([
            'acronym' => 'required|string|max:255',
            'hospitalName' => 'required|string|max:255',
            'location' => 'nullable|max:255',
            'contactPerson' => 'nullable|max:255',
            
        ]);
        $validated['status'] = 'active';
        $hospitals = Hospital::create($validated);
        $hospitals->load(['contact_person', 'hospital_location']);
        return response()->json([
            'hospitalId' => $hospitals->hospitalId,
            'acronym' => $hospitals->acronym,
            'hospitalName' => $hospitals->hospitalName,
            // 'contactPerson' => $hospitals->contactPerson,
            'contactPerson' => $hospitals->contactPerson ? $hospitals->contact_person->firstName : null,
            'location' => $hospitals->location ? $hospitals->hospital_location->stateName : null,
    ], 201); // HTTP status code 201: Created

    }

   public function edit(Request $request, $hospitalId)
{
    $validated = $request->validate([
        'hospitalName' => 'nullable|string|max:255',
        'acronym' => 'nullable|string|max:255',
        'location' => 'nullable|max:255',
        'contactPerson' => 'nullable|max:255',
    ]);

    $hospital = Hospital::with(['contact_person', 'hospital_location'])->where('hospitalId', $hospitalId)->first();
    if (!$hospital) {
        return response()->json(['message' => 'Hospital type not found'], 404);
    }

    $hospital->update($validated);
    
    return response()->json([
        'hospitalId' => $hospital->hospitalId,
        'hospitalName' => $hospital->hospitalName,
        'acronym' => $hospital->acronym,
        'status' => $hospital->status,
        // 'contactPerson' => $hospital->contactPerson ? $hospital->contact_person->firstName : null,
        'contactPerson' => $hospital->contact_person ? $hospital->contact_person->firstName : null, // Check relationship
        'location' => $hospital->hospital_location ? $hospital->hospital_location->stateName : null,
    ], 200);
}

    public function destroy($hospitalId)
    {
        $hospital = Hospital::where('hospitalId', $hospitalId)->first();
        if (!$hospital) {
            return response()->json(['message' => 'Hospital type not found'], 404);
        }

        $hospital->delete();
        return response()->json(['message' => 'Hospital type deleted successfully'], 200);
    }
    public function show($hospitalId)
    {
        $hospital = Hospital::where('hospitalId', $hospitalId)->first();
        if (!$hospital) {
            return response()->json(['message' => 'Hospital type not found'], 404);
        }
        return response()->json($hospital);
    }
   
}
