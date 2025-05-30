<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\StockMovement as ModelsStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockMovement extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all stock movements with pagination
        $stockMovements = ModelsStockMovement::with('productId')->paginate(10);

        // Return the stock movements as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Stock movements retrieved successfully',
            'data' => $stockMovements,
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
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        // If validation fails, return a JSON response with errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // if type is in, add quantity to product stock and if type is out, subtract quantity from product stock and check if stock is sufficient
        $product = Products::find($request->input('product_id'));
        if ($request->input('type') === 'in') {
            $product->stock += $request->input('quantity');
        } elseif ($request->input('type') === 'out') {
            if ($product->stock < $request->input('quantity')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock',
                ], 422);
            }
            $product->stock -= $request->input('quantity');
        }
        // Save the product stock
        $product->save();

        // Create a new stock movement
        $stockMovement = ModelsStockMovement::create([
            'product_id' => $request->input('product_id'),
            'type' => $request->input('type'),
            'quantity' => $request->input('quantity'),
            'note' => $request->input('note'),
            'purchase_item_id' => $request->input('purchase_item_id'),
            'sales_item_id' => $request->input('sales_item_id'),
        ]);

        // Return a JSON response with the created stock movement
        return response()->json([
            'success' => true,
            'message' => 'Stock movement created successfully',
            'data' => $stockMovement,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Fetch the stock movement by ID
        $stockMovement = ModelsStockMovement::with('product')->find($id);
        // If not found, return a 404 response
        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found',
            ], 404);
        }

        // Return the stock movement as a JSON response
        return response()->json([
            'success' => true,
            'message' => 'Stock movement retrieved successfully',
            'data' => $stockMovement,
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
        // Fetch the stock movement by ID
        $stockMovement = ModelsStockMovement::find($id);
        // If not found, return a 404 response
        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found',
            ], 404);
        }

        // Ambil data lama
        $oldProduct = Products::findOrFail($stockMovement->product_id);
        $oldQuantity = (int) $stockMovement->quantity;
        $oldType = $stockMovement->type;

        // Rollback stok lama
        if ($oldType === 'in') {
            $oldProduct->stock -= $oldQuantity;
        } elseif ($oldType === 'out') {
            $oldProduct->stock += $oldQuantity;
        }
        $oldProduct->save();

        // Ambil data baru dari input
        $newProduct = Products::findOrFail($request->input('product_id'));
        $newQuantity = (int) $request->input('quantity');
        $newType = $request->input('type');

        // Terapkan stok baru
        if ($newType === 'in') {
            $newProduct->stock += $newQuantity;
        } elseif ($newType === 'out') {
            if ($newProduct->stock < $newQuantity) {
                // Jika tidak cukup stok, rollback perubahan ke produk lama
                $oldProduct->stock += ($oldType === 'in') ? $oldQuantity : -$oldQuantity;
                $oldProduct->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock on new product',
                ], 422);
            }
            $newProduct->stock -= $newQuantity;
        }

        $newProduct->save();

        // Update the stock movement
        $stockMovement->update([
            'product_id' => $request->input('product_id'),
            'type' => $request->input('type'),
            'quantity' => $request->input('quantity'),
            'note' => $request->input('note'),
        ]);
        // Return a JSON response with the updated stock movement
        return response()->json([
            'success' => true,
            'message' => 'Stock movement updated successfully',
            'data' => $stockMovement,
        ]);
    }

    public function updateByPurchaseItemId(Request $request, string $id)
    {
        $stockMovement = ModelsStockMovement::where('purchase_item_id', $id)->first();

        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found',
            ], 404);
        }

        $oldProductId = $stockMovement->product_id;
        $oldProduct = Products::findOrFail($oldProductId);
        $oldQuantity = (int) $stockMovement->quantity;
        $oldType = $stockMovement->type;

        // Rollback stok lama
        if ($oldType === 'in') {
            $oldProduct->stock -= $oldQuantity;
        } elseif ($oldType === 'out') {
            $oldProduct->stock += $oldQuantity;
        }
        $oldProduct->save();

        // Ambil data baru dari request
        $newProductId = $request->input('product_id');
        $newProduct = Products::findOrFail($newProductId);
        $newQuantity = (int) $request->input('quantity');
        $newType = $request->input('type');

        // Cek jika product_id berubah
        $isDifferentProduct = $oldProductId != $newProductId;

        // Validasi stok produk baru jika type 'out'
        if ($newType === 'out' && $newProduct->stock < $newQuantity) {
            // Kembalikan stok lama karena gagal update
            if ($oldType === 'in') {
                $oldProduct->stock += $oldQuantity;
            } elseif ($oldType === 'out') {
                $oldProduct->stock -= $oldQuantity;
            }
            $oldProduct->save();

            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock on new product',
            ], 422);
        }

        // Terapkan perubahan stok ke produk baru
        if ($newType === 'in') {
            $newProduct->stock += $newQuantity;
        } elseif ($newType === 'out') {
            $newProduct->stock -= $newQuantity;
        }
        $newProduct->save();

        // Update record stock movement
        $stockMovement->update([
            'product_id' => $newProductId,
            'type' => $newType,
            'quantity' => $newQuantity,
            'note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock movement updated successfully',
            'data' => $stockMovement,
        ]);
    }

    public function updateBySalesItemId(Request $request, string $id)
    {
        $stockMovement = ModelsStockMovement::where('sales_item_id', $id)->first();

        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found',
            ], 404);
        }

        $oldProductId = $stockMovement->product_id;
        $oldProduct = Products::findOrFail($oldProductId);
        $oldQuantity = (int) $stockMovement->quantity;
        $oldType = $stockMovement->type;

        // Rollback stok lama
        if ($oldType === 'in') {
            $oldProduct->stock -= $oldQuantity;
        } elseif ($oldType === 'out') {
            $oldProduct->stock += $oldQuantity;
        }
        $oldProduct->save();

        // Ambil data baru dari request
        $newProductId = $request->input('product_id');
        $newProduct = Products::findOrFail($newProductId);
        $newQuantity = (int) $request->input('quantity');
        $newType = $request->input('type');

        // Cek jika product_id berubah
        $isDifferentProduct = $oldProductId != $newProductId;

        // Validasi stok produk baru jika type 'out'
        if ($newType === 'out' && $newProduct->stock < $newQuantity) {
            // Kembalikan stok lama karena gagal update
            if ($oldType === 'in') {
                $oldProduct->stock += $oldQuantity;
            } elseif ($oldType === 'out') {
                $oldProduct->stock -= $oldQuantity;
            }
            $oldProduct->save();

            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock on new product',
            ], 422);
        }

        // Terapkan perubahan stok ke produk baru
        if ($newType === 'in') {
            $newProduct->stock += $newQuantity;
        } elseif ($newType === 'out') {
            $newProduct->stock -= $newQuantity;
        }
        $newProduct->save();

        // Update record stock movement
        $stockMovement->update([
            'product_id' => $newProductId,
            'type' => $newType,
            'quantity' => $newQuantity,
            'note' => $request->input('note'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock movement updated successfully',
            'data' => $stockMovement,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Fetch the stock movement by ID
        $stockMovement = ModelsStockMovement::find($id);
        // If not found, return a 404 response
        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found',
            ], 404);
        }

        // Rollback the stock movement
        $product = Products::find($stockMovement->product_id);
        if ($stockMovement->type === 'in') {
            $product->stock -= $stockMovement->quantity;
        } elseif ($stockMovement->type === 'out') {
            $product->stock += $stockMovement->quantity;
        }
        $product->save();

        // Delete the stock movement
        $stockMovement->delete();

        // Return a JSON response indicating success
        return response()->json([
            'success' => true,
            'message' => 'Stock movement deleted successfully',
        ]);
    }

    public function destroyByPurchaseItemId(string $id)
    {
        // Ambil stock movement berdasarkan purchase_item_id
        $stockMovement = ModelsStockMovement::where('purchase_item_id', $id)->first();

        if (!$stockMovement) {
            return response()->json([
                'success' => false,
                'message' => 'Stock movement not found.',
            ], 404);
        }

        // Ambil produk terkait
        $product = Products::find($stockMovement->product_id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product associated with stock movement not found.',
            ], 404);
        }

        // Rollback stok sesuai tipe
        switch ($stockMovement->type) {
            case 'in':
                if ($product->stock < $stockMovement->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rollback would cause negative stock.',
                    ], 422);
                }
                $product->stock -= $stockMovement->quantity;
                break;

            case 'out':
                $product->stock += $stockMovement->quantity;
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid stock movement type.',
                ], 400);
        }

        $product->save();
        $stockMovement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stock movement deleted and stock updated successfully.',
        ]);
    }
}
