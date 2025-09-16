<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all services with pagination
        $services = Service::paginate(10);

        // Return the services as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Services retrieved successfully',
            'data' => $services,
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
            'unit_name' => 'required|string|max:255',
            'deskripsi_kerusakan' => 'required|string|max:255',
            'nama_pemilik' => 'required|string|max:255',
            'tanggal_perbaikan' => 'required|date',
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
        $employee = Service::create([
            'unit_name' => $request->input('unit_name'),
            'deskripsi_kerusakan' => $request->input('deskripsi_kerusakan'),
            'nama_pemilik' => $request->input('nama_pemilik'),
            'tanggal_perbaikan' => $request->input('tanggal_perbaikan'),
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $employee,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        // Fetch the service by ID
        $service = Service::find($id);

        // If service not found, return a 404 response
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
            ], 404);
        }

        // Update the service with the validated data
        $service->update($request->only(['unit_name', 'deskripsi_kerusakan', 'nama_pemilik', 'tanggal_perbaikan']));

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       //
    }
}
