<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchases;
use App\Models\Sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all purchases with pagination
        $purchases = Purchases::with('supplierId')->paginate(100);
        // Return the purchases as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchases retrieved successfully',
            'data' => $purchases,
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
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'status' => 'required|in:pending,received,canceled',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create a new purchase with total_amount set to 0
        $data = $request->all();
        $data['total_amount'] = 0;
        $purchase = Purchases::create($data);

        // Return the created purchase as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase created successfully',
            'data' => $purchase,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the purchase by ID
        $purchase = Purchases::with('supplierId')->find($id);
        // If purchase not found, return a JSON response with an error message
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found',
            ], 404);
        }

        // Return the purchase as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase retrieved successfully',
            'data' => $purchase,
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
        // Fetch the purchase by ID
        $purchase = Purchases::find($id);
        // If purchase not found, return a JSON response with an error message
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'purchase_date' => 'sometimes|required|date',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:pending,completed,cancelled',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }
        // Update the purchase with the request data
        $purchase->update($request->all());
        // Return the updated purchase as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase updated successfully',
            'data' => $purchase,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Fetch the purchase by ID
        $purchase = Purchases::find($id);
        // If purchase not found, return a JSON response with an error message
        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase not found',
            ], 404);
        }
        // Delete the purchase
        $purchase->delete();
        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Purchase deleted successfully',
        ]);
    }

    public function totalPerDate(Request $request)
    {
        // --- OPTIONAL FILTERS ---
        $date       = $request->query('date');        // YYYY-MM-DD
        $startDate  = $request->query('start_date');  // YYYY-MM-DD
        $endDate    = $request->query('end_date');    // YYYY-MM-DD
        $month      = $request->query('month');       // 11
        $year       = $request->query('year');        // 2025

        // Query Purchase
        $purchaseQuery = Purchases::selectRaw('purchase_date as date, SUM(total_amount) as total_purchase')
            ->groupBy('purchase_date')
            ->orderBy('purchase_date', 'desc');

        // Query Sales
        $salesQuery = Sales::selectRaw('sales_date as date, SUM(sales_amount) as total_sales')
            ->groupBy('sales_date')
            ->orderBy('sales_date', 'desc');


        // --- APPLY FILTERS ---
        if ($date) {
            $purchaseQuery->whereDate('purchase_date', $date);
            $salesQuery->whereDate('sales_date', $date);
        }

        if ($startDate && $endDate) {
            $purchaseQuery->whereBetween('purchase_date', [$startDate, $endDate]);
            $salesQuery->whereBetween('sales_date', [$startDate, $endDate]);
        }

        if ($month) {
            $purchaseQuery->whereMonth('purchase_date', $month);
            $salesQuery->whereMonth('sales_date', $month);
        }

        if ($year) {
            $purchaseQuery->whereYear('purchase_date', $year);
            $salesQuery->whereYear('sales_date', $year);
        }

        // Execute Queries
        $purchaseData = $purchaseQuery->get();
        $salesData = $salesQuery->get();

        // --- MERGE DATA ---
        // Jadikan tanggal sebagai key
        $combined = [];

        foreach ($purchaseData as $p) {
            $combined[$p->date] = [
                'date'           => $p->date,
                'total_purchase' => $p->total_purchase,
                'total_sales'    => "0",
            ];
        }

        foreach ($salesData as $s) {
            if (!isset($combined[$s->date])) {
                $combined[$s->date] = [
                    'date'           => $s->date,
                    'total_purchase' => "0",
                    'total_sales'    => $s->total_sales,
                ];
            } else {
                $combined[$s->date]['total_sales'] = $s->total_sales;
            }
        }

        // Ubah jadi array terurut berdasarkan tanggal
        krsort($combined); // sort descending

        return response()->json([
            'success' => true,
            'message' => 'Purchase + Sales total per date',
            'data'    => array_values($combined),
        ]);
    }
}
