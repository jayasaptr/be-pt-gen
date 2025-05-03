<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payrolls;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all payroll records with pagination
        $payrolls = Payrolls::with('employeeId')->paginate(10);
        // Return the payroll records as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Payroll records retrieved successfully',
            'data' => $payrolls,
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
            'employee_id' => 'required|exists:employees,id',
            'pay_period' => 'required|date',
            'basic_salary' => 'required|numeric',
            'deductions' => 'nullable|numeric',
            'bonuses' => 'nullable|numeric',
            'total_paid' => 'required|numeric',
            'status' => 'required|string',
            'paid_at' => 'nullable|date',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new payroll record
        $payroll = Payrolls::create([
            'employee_id' => $request->input('employee_id'),
            'pay_period' => $request->input('pay_period'),
            'basic_salary' => $request->input('basic_salary'),
            'deductions' => $request->input('deductions'),
            'bonuses' => $request->input('bonuses'),
            'total_paid' => $request->input('total_paid'),
            'status' => $request->input('status'),
            'paid_at' => $request->input('paid_at'),
        ]);
        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Payroll record created successfully',
            'data' => $payroll,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the payroll record by ID
        $payroll = Payrolls::with('employeeId')->find($id);
        // If the payroll record is not found, return a 404 response
        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found',
            ], 404);
        }

        // Return the payroll record as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Payroll record retrieved successfully',
            'data' => $payroll,
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
        // 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the payroll record by ID
        $payroll = Payrolls::find($id);
        // If the payroll record is not found, return a JSON response with an error message
        if (!$payroll) {
            return response()->json([
                'status' => false,
                'message' => 'Payroll record not found',
            ], 404);
        }
        // Delete the payroll record
        $payroll->delete();
        // Return a JSON response indicating success
        return response()->json([
            'status' => true,
            'message' => 'Payroll record deleted successfully',
        ], 200);
    }
}
