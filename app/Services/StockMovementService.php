<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Products;

class StockMovementService
{
    public function rollbackStockMovementByPurchaseItemId(string $purchaseItemId): bool|string
    {
        $stockMovement = StockMovement::where('purchase_item_id', $purchaseItemId)->first();

        if (!$stockMovement) return 'Stock movement not found';

        $product = Products::find($stockMovement->product_id);
        if (!$product) return 'Product not found';

        if ($stockMovement->type == 'in') {
            if ($product->stock < $stockMovement->quantity) return 'Stock rollback would result in negative stock';
            $product->stock -= $stockMovement->quantity;
        } elseif ($stockMovement->type == 'out') {
            $product->stock += $stockMovement->quantity;
        }

        $product->save();
        $stockMovement->delete();

        return true;
    }

    public function rollbackStockMovementBySalesItemId(string $salesItemId): bool|string
    {
        $stockMovement = StockMovement::where('sales_item_id', $salesItemId)->first();

        if (!$stockMovement) return 'Stock movement not found';

        $product = Products::find($stockMovement->product_id);
        if (!$product) return 'Product not found';

        if ($stockMovement->type == 'out') {
            $product->stock += $stockMovement->quantity;
        } elseif ($stockMovement->type == 'in') {
            $product->stock -= $stockMovement->quantity;
        }

        $product->save();
        $stockMovement->delete();

        return true;
    }
}
