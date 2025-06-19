<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Staff; 
use App\Models\StaffType;
class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('staff.staff_type')->get();
        return response()->json($users);
       
    }

  public function supervisors()
{
    $users = User::with('staff.staff_type')
        ->whereHas('staff', function ($query) {
            $query->where('staffType', 2);
        })
        ->get();

    return response()->json($users);
}


    public function staff_type()
    {
        $staffTypes = StaffType::all();
        return response()->json($staffTypes);
       
    }

    public function store(Request $request)
    {
        // Directly get the data from the request
        $data = $request->all();
    
        // Create a new user with the data (ensure that the fields are mass assignable in the model)
        $roles = Roles::create($data);
    
        // Return a response, typically JSON
        return response()->json($roles, 201); // HTTP status code 201: Created
    }
    
}
