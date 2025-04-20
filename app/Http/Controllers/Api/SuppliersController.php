<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all suppliers with pagination
        $suppliers = Supplier::paginate(10);

        // Return the suppliers as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Suppliers retrieved successfully',
            'data' => $suppliers,
        ]);
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
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new supplier
        $supplier = Supplier::create($request->all());
        // Return the created supplier as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'data' => $supplier,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the supplier by ID
        $supplier = Supplier::find($id);

        // If supplier not found, return a JSON response with an error message
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found',
            ], 404);
        }
        // Return the supplier as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Supplier retrieved successfully',
            'data' => $supplier,
        ]);
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
        // Fetch the supplier by ID
        $supplier = Supplier::find($id);

        // If supplier not found, return a JSON response with an error message
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found',
            ], 404);
        }

        // Update the supplier with the request data
        $supplier->update($request->all());
        // Return the updated supplier as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'data' => $supplier,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Fetch the supplier by ID
        $supplier = Supplier::find($id);
        // If supplier not found, return a JSON response with an error message

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found',
            ], 404);
        }

        // Delete the supplier
        $supplier->delete();
        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
