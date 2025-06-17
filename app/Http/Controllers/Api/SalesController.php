<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all sales from the database with pagination
        $sales = Sales::with('customerId')->paginate(10);

        // Return the sales as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Sales retrieved successfully',
            'data' => $sales,
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
            'customer_id' => 'required|exists:customers,id',
            'sales_date' => 'required|date',
            'sales_status' => 'required|in:draft,paid,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors(),
            ], 422);
        }

        // Create a new sale
        $sales = Sales::create([
            'customer_id' => $request->customer_id,
            'sales_date' => $request->sales_date,
            'sales_amount' => 0,
            'sales_status' => $request->sales_status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Sales created successfully',
            'data' => $sales,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the sale by ID
        $sales = Sales::with('customer')->find($id);

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Sales not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Sales retrieved successfully',
            'data' => $sales,
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
        // Find the sale by ID
        $sales = Sales::find($id);

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Sales not found',
            ], 404);
        }
        // Update the sale
        $sales->update([
            'customer_id' => $request->customer_id ?? $sales->customer_id,
            'sales_date' => $request->sales_date ?? $sales->sales_date,
            'sales_amount' => $request->sales_amount ?? $sales->sales_amount,
            'sales_status' => $request->sales_status ?? $sales->sales_status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Sales updated successfully',
            'data' => $sales,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the sale by ID
        $sales = Sales::find($id);

        if (!$sales) {
            return response()->json([
                'status' => false,
                'message' => 'Sales not found',
            ], 404);
        }

        // Delete the sale
        $sales->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sales deleted successfully',
        ], 200);
    }
}
