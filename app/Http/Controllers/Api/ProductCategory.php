<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductCategory extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all product categories with pagination
        $categories = ProductCategories::paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Product categories retrieved successfully',
            'data' => $categories,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'data' => $validator->errors(),
            ], 422);
        }

        // Create a new product category
        $category = ProductCategories::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the product category by ID
        $category = ProductCategories::find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Product category not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Product category retrieved successfully',
            'data' => $category,
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
        // Find the product category by ID
        $category = ProductCategories::find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Product category not found',
            ], 404);
        }

        // Update the product category
        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product category updated successfully',
            'data' => $category,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the product category by ID

        $category = ProductCategories::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Product category not found',
            ], 404);
        }

        // Delete the product category
        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product category deleted successfully',
        ], 200);
    }
}
