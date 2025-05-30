<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseItem;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchaseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil parameter purchase_id jika ada
        $purchaseId = $request->query('purchase_id');

        // Query dasar dengan relasi
        $query = PurchaseItem::with('purchaseId', 'productId');

        // Jika ada purchase_id, filter berdasarkan purchase_id
        if ($purchaseId) {
            $query->where('purchase_id', $purchaseId);
        }

        // Paginasi hasil
        $purchaseItems = $query->paginate(10);

        // Return response JSON
        return response()->json([
            'success' => true,
            'message' => 'Purchase items retrieved successfully',
            'data' => $purchaseItems,
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
            'purchase_id' => 'required|exists:purchases,id',
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

        // Hitung total otomatis
        $total = $request->input('price') * $request->input('quantity');

        // Simpan ke PurchaseItem
        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $request->input('purchase_id'),
            'product_id' => $request->input('product_id'),
            'quantity' => $request->input('quantity'),
            'price' => $request->input('price'),
            'total' => $total,
        ]);

        // store to stock movement
        $stockMovementController = new StockMovement();
        $stockRequest = new Request([
            'product_id' => $request->input('product_id'),
            'type' => 'in',
            'quantity' => $request->input('quantity'),
            'note' => $request->note ?? 'Pembelian otomatis dari purchase ID ' . $request->input('purchase_id'),
            'purchase_item_id' => $purchaseItem->id,
        ]);

        $stockMovementController->store($stockRequest);

        // Return the created purchase item as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase item created successfully',
            'data' => $purchaseItem,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the purchase item by ID
        $purchaseItem = PurchaseItem::with('purchaseId', 'productId')->find($id);
        // If purchase item not found, return a 404 response
        if (!$purchaseItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase item not found',
            ], 404);
        }

        // Return the purchase item as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase item retrieved successfully',
            'data' => $purchaseItem,
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
        // Fetch the purchase item by ID
        $purchaseItem = PurchaseItem::find($id);
        // If purchase item not found, return a 404 response
        if (!$purchaseItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase item not found',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'purchase_id' => 'sometimes|required|exists:purchases,id',
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

        // Update the purchase item with the validated data
        $purchaseItem->update($request->only(['purchase_id', 'product_id', 'quantity', 'price']));
        // Recalculate total
        $total = $request->input('price') * $request->input('quantity');
        $purchaseItem->update(['total' => $total]);

        // update stock movement
        $stockMovementController = new StockMovement();
        $stockRequest = new Request([
            'product_id' => $request->input('product_id'),
            'type' => 'in',
            'quantity' => $request->input('quantity'),
            'note' => 'Pembelian otomatis dari purchase ID ' . $request->input('purchase_id'),
        ]);
        $stockMovementController->updateByPurchaseItemId($stockRequest, $purchaseItem->id);

        // Return the updated purchase item as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Purchase item updated successfully',
            'data' => $purchaseItem,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, StockMovementService $stockService)
    {
        // Fetch the purchase item by ID
        $purchaseItem = PurchaseItem::find($id);
        // If purchase item not found, return a 404 response
        if (!$purchaseItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase item not found',
            ], 404);
        }

        $result = $stockService->rollbackStockMovementByPurchaseItemId($purchaseItem->id);

        if ($result !== true) {
            return response()->json(['success' => false, 'message' => $result], 422);
        }

        // Delete the purchase item
        $purchaseItem->delete();
        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Purchase item deleted successfully',
        ]);
    }
}
