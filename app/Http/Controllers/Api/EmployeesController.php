<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all employees with pagination
        $employees = Employees::paginate(10);

        // Return the employees as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Employees retrieved successfully',
            'data' => $employees,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'required|string|max:15',
            'position' => 'required|string|max:100',
            'salary' => 'required|numeric|min:0',
            'join_date' => 'required|date',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new employee
        $employee = Employees::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'position' => $request->input('position'),
            'salary' => $request->input('salary'),
            'join_date' => $request->input('join_date'),
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the employee by ID
        $employee = Employees::find($id);

        // If employee not found, return a 404 response
        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Return the employee as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Employee retrieved successfully',
            'data' => $employee,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Fetch the employee by ID
        $employee = Employees::find($id);

        // If employee not found, return a 404 response
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Update the employee with the validated data
        $employee->update($request->only(['name', 'email', 'phone', 'position', 'salary', 'join_date']));

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Fetch the employee by ID
        $employee = Employees::find($id);
        // If employee not found, return a 404 response

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Delete the employee
        $employee->delete();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully',
        ], 200);
    }
}
