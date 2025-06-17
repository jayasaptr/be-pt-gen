<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\SalesItem;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get sales_id from query if present
        $salesId = $request->query('sales_id');

        // Query sales items, filter by sales_id if provided
        $query = SalesItem::query();

        if ($salesId) {
            $query->where('sales_id', $salesId);
        }

        // Fetch sales items with pagination
        $salesItems = $query->with(['salesId.customerId', 'productId'])->paginate(10);

        // Return the sales items as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Sales retrieved successfully',
            'data' => $salesItems,
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
            'sales_id' => 'required|exists:sales,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Products::find($request->input('product_id'));
        if ($product->stock < $request->input('quantity')) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock',
            ], 422);
        }

        // Calculate subtotal automatically
        $subtotal = $request->input('price') * $request->input('quantity');

        // Save to SalesItem
        $salesItem = SalesItem::create([
            'sales_id' => $request->input('sales_id'),
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'price' => $request->input('price'),
            'subtotal' => $subtotal,
        ]);


        // Store to stock movement
        // $stockMovementController = new StockMovement();
        // $stockRequest = new Request([
        //     'product_id' => $request->input('product_id'),
        //     'type' => 'out',
        //     'quantity' => $request->input('quantity'),
        //     'note' => $request->note ?? 'Penjualan otomatis dari sales ID ' . $request->input('sales_id'),
        //     'sales_item_id' => $salesItem->id,
        // ]);

        // $stockMovementController->store($stockRequest);

        //tambah sales_amount pada sales
        $sales = $salesItem->salesId;
        $sales->sales_amount += $subtotal;
        $sales->save();

        // store to stock movement
        $stockMovementController = new StockMovement();
        $stockRequest = new Request([
            'product_id' => $request->input('product_id'),
            'type' => 'out',
            'quantity' => $request->input('quantity'),
            'note' => $request->note ?? 'penjualan otomatis dari purchase ID ' . $request->input('sales_id'),
            'sales_item_id' => $salesItem->id,
        ]);

        $stockMovementController->store($stockRequest);

        // Return the created sales item as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Sales item created successfully',
            'data' => $salesItem,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find the sales item by ID
        $salesItem = SalesItem::with('salesId', 'productId')->find($id);

        // If the sales item is not found, return a 404 response
        if (!$salesItem) {
            return response()->json([
                'status' => false,
                'message' => 'Sales item not found',
            ], 404);
        }

        // Return the sales item as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Sales item retrieved successfully',
            'data' => $salesItem,
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
        // Fetch the sales item by ID
        $salesItem = SalesItem::find($id);

        // If sales item not found, return a 404 response
        if (!$salesItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales item not found',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'sales_id' => 'sometimes|required|exists:sales,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update the sales item with the validated data
        $salesItem->update($request->only(['sales_id', 'product_id', 'quantity', 'price']));

        // Recalculate subtotal
        $subtotal = $request->input('price') * $request->input('quantity');
        $salesItem->update(['subtotal' => $subtotal]);

        // Update stock movement
        $stockMovementController = new StockMovement();
        $stockRequest = new Request([
            'product_id' => $request->input('product_id'),
            'type' => 'out',
            'quantity' => $request->input('quantity'),
            'note' => 'Penjualan otomatis dari sales ID ' . $request->input('sales_id'),
        ]);
        $stockMovementController->updateBySalesItemId($stockRequest, $salesItem->id);

        // Return the updated sales item as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Sales item updated successfully',
            'data' => $salesItem,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, StockMovementService $stockService)
    {
        // Fetch the sales item by ID
        $salesItem = SalesItem::find($id);

        // If sales item not found, return a 404 response
        if (!$salesItem) {
            return response()->json([
                'success' => false,
                'message' => 'Sales item not found',
            ], 404);
        }

        // Rollback stock movement
        $result = $stockService->rollbackStockMovementBySalesItemId($salesItem->id);

        if ($result !== true) {
            return response()->json(['success' => false, 'message' => $result], 422);
        }

        // Delete the sales item
        $salesItem->delete();

        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Sales item deleted successfully',
        ]);
    }
}
