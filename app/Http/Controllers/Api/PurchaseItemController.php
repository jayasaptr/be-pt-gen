<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kas;
use App\Models\PurchaseItem;
use App\Models\Purchases;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
            'price'       => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            // Hitung total otomatis
            $total = $request->input('price') * $request->input('quantity');

            // Simpan ke PurchaseItem
            $purchaseItem = PurchaseItem::create([
                'purchase_id' => $request->input('purchase_id'),
                'product_id'  => $request->input('product_id'),
                'quantity'    => $request->input('quantity'),
                'price'       => $request->input('price'),
                'total'       => $total,
            ]);

            // Tambah total_amount pada purchase (pakai relasi yg benar)
            // pastikan di model PurchaseItem ada: public function purchase() { return $this->belongsTo(Purchase::class); }
            $purchase = $purchaseItem->purchase;
            if ($purchase) {
                $purchase->total_amount += $total;
                $purchase->save();
            }

            // Store ke stock movement (pakai controller/servis yang sudah kamu punya)
            $stockMovementController = new StockMovement(); // idealnya service, tapi dibiarkan sesuai kode kamu
            $stockRequest = new Request([
                'product_id'       => $request->input('product_id'),
                'type'             => 'in',
                'quantity'         => $request->input('quantity'),
                'note'             => $request->note ?? 'Pembelian otomatis dari purchase ID ' . $request->input('purchase_id'),
                'purchase_item_id' => $purchaseItem->id,
            ]);
            $stockMovementController->store($stockRequest);

            // Update kas (gunakan 1 baris saja sebagai saldo / akumulasi)
            // kalau tabel kas kosong, buat dengan default in = 0
            $kas = Kas::firstOrCreate([], ['out' => 0]);
            $kas->out += $total; // logika kamu sekarang: IN ditambah saat store
            $kas->save();

            return response()->json([
                'success' => true,
                'message' => 'Purchase item created successfully',
                'data'    => $purchaseItem,
            ], 201);
        });
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
        // Cari purchase item
        $purchaseItem = PurchaseItem::find($id);

        if (!$purchaseItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase item not found',
            ], 404);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'purchase_id' => 'sometimes|required|exists:purchases,id',
            'product_id'  => 'sometimes|required|exists:products,id',
            'quantity'    => 'sometimes|required|integer|min:1',
            'price'       => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors'  => $validator->errors(),
            ], 422);
        }

        return DB::transaction(function () use ($request, $purchaseItem) {
            // Simpan nilai lama
            $oldTotal       = $purchaseItem->total;
            $oldPurchaseId  = $purchaseItem->purchase_id;
            $oldProductId   = $purchaseItem->product_id;
            $oldQty         = $purchaseItem->quantity;
            $oldPrice       = $purchaseItem->price;

            // Ambil nilai baru (kalau tidak dikirim, pakai nilai lama)
            $newPurchaseId = $request->input('purchase_id', $oldPurchaseId);
            $newProductId  = $request->input('product_id', $oldProductId);
            $newQty        = $request->input('quantity', $oldQty);
            $newPrice      = $request->input('price', $oldPrice);

            $newTotal = $newPrice * $newQty;

            // Update purchase_item
            $purchaseItem->update([
                'purchase_id' => $newPurchaseId,
                'product_id'  => $newProductId,
                'quantity'    => $newQty,
                'price'       => $newPrice,
                'total'       => $newTotal,
            ]);

            // Update total_amount di purchase
            if ($oldPurchaseId == $newPurchaseId) {
                // Sama purchase, cukup adjust selisih
                $purchase = $purchaseItem->purchase; // relasi
                if ($purchase) {
                    $purchase->total_amount -= $oldTotal;
                    $purchase->total_amount += $newTotal;
                    $purchase->save();
                }
            } else {
                // Pindah ke purchase lain
                $oldPurchase = Purchases::find($oldPurchaseId);
                $newPurchase = Purchases::find($newPurchaseId);

                if ($oldPurchase) {
                    $oldPurchase->total_amount -= $oldTotal;
                    $oldPurchase->save();
                }
                if ($newPurchase) {
                    $newPurchase->total_amount += $newTotal;
                    $newPurchase->save();
                }
            }

            // Update stock movement berdasarkan purchase_item_id
            $stockMovementController = new StockMovement(); // sesuai kode kamu
            $stockRequest = new Request([
                'product_id' => $newProductId,
                'type'       => 'in',
                'quantity'   => $newQty,
                'note'       => $request->note ?? 'Update pembelian dari purchase ID ' . $newPurchaseId,
            ]);

            // Pastikan di controller StockMovement ada method: updateByPurchaseItemId($request, $purchaseItemId)
            $stockMovementController->updateByPurchaseItemId($stockRequest, $purchaseItem->id);

            // Update kas: pakai selisih
            $delta = $newTotal - $oldTotal; // kalau naik, kas.in ikut naik; kalau turun, kas.in ikut turun

            $kas = Kas::firstOrCreate([], ['out' => 0]);
            $kas->out += $delta;
            $kas->save();

            return response()->json([
                'success' => true,
                'message' => 'Purchase item updated successfully',
                'data'    => $purchaseItem,
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, StockMovementService $stockService)
    {
        // Cari purchase item
        $purchaseItem = PurchaseItem::find($id);

        if (!$purchaseItem) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase item not found',
            ], 404);
        }

        return DB::transaction(function () use ($purchaseItem, $stockService) {

            // Rollback pergerakan stok
            $result = $stockService->rollbackStockMovementByPurchaseItemId($purchaseItem->id);

            if ($result !== true) {
                return response()->json([
                    'success' => false,
                    'message' => $result,
                ], 422);
            }

            // Kurangi total_amount di purchase
            $purchase = $purchaseItem->purchase; // relasi
            if ($purchase) {
                $purchase->total_amount -= $purchaseItem->total;
                $purchase->save();
            }

            // Kurangi kas.in sesuai total purchase item yang dihapus
            $kas = Kas::firstOrCreate([], ['out' => 0]);
            $kas->out -= $purchaseItem->total;
            $kas->save();

            // Terakhir, hapus purchase item
            $purchaseItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase item deleted successfully',
            ]);
        });
    }
}
