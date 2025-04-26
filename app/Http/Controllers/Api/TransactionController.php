<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all transactions with pagination
        $transactions = Transactions::with('salesId', 'purchaseId')->paginate(10);

        // Return the transactions as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => $transactions,
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
            'transaction_type' => 'required|string',
            'reference_type' => 'required|string',
            'reference_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new transaction
        $transaction = Transactions::create([
            'transaction_type' => $request->input('transaction_type'),
            'reference_type' => $request->input('reference_type'),
            'reference_id' => $request->input('reference_id'),
            'amount' => $request->input('amount'),
            'description' => $request->input('description'),
            'transaction_date' => $request->input('transaction_date'),
        ]);

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => $transaction,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the transaction by ID
        $transaction = Transactions::with('salesId', 'purchaseId')->find($id);

        // If transaction not found, return a 404 response
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Return the transaction as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => $transaction,
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
        // Fetch the transaction by ID
        $transaction = Transactions::find($id);
        // If transaction not found, return a 404 response
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Update the transaction with the validated data
        $transaction->update($request->only([
            'transaction_type',
            'reference_type',
            'reference_id',
            'amount',
            'description',
            'transaction_date'
        ]));

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => $transaction,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Fetch the transaction by ID
        $transaction = Transactions::find($id);

        // If transaction not found, return a 404 response
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        // Delete the transaction
        $transaction->delete();
        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ], 200);
    }
}
