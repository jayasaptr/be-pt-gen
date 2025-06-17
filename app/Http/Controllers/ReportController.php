<?php

namespace App\Http\Controllers;

use App\Models\Attendances;
use App\Models\Employees;
use App\Models\Payrolls;
use App\Models\Products;
use App\Models\PurchaseItem;
use App\Models\Purchases;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function getReportBarangTersedia(Request $request)
    {
        // search query parameter
        $search = $request->query('search', '');
        // If search is provided, filter products by name

        $query = Products::select('id', 'name', 'stock')
            ->where('stock', '>', 0);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->stock, // Convert stock to integer
                ];
            }),
        ]);
    }

    public function getReportBarangTerjual(Request $request)
    {
        // search query parameter
        $search = $request->query('search', '');
        // If search is provided, filter products by name

        $query = Products::select('id', 'name', 'stock')
            ->where('stock', '<=', 0);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $products = $query->orderBy('name')->get();

        return response()->json([
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => (int) $product->stock, // Convert stock to integer
                ];
            }),
        ]);
    }

    public function getReportPembelian(Request $request)
    {
        $searchDate = $request->query('date', '');
        $searchSupplier = $request->query('supplier', '');

        $purchasesQuery = Purchases::with(['supplierId']);

        if (!empty($searchDate)) {
            $purchasesQuery->whereDate('created_at', $searchDate);
        }

        if (!empty($searchSupplier)) {
            $purchasesQuery->whereHas('supplierId', function ($q) use ($searchSupplier) {
                $q->where('name', 'like', '%' . $searchSupplier . '%');
            });
        }

        $purchases = $purchasesQuery->orderBy('created_at', 'desc')->get();

        $purchasesItem = PurchaseItem::with(['purchaseId', 'productId'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Purchase report retrieved successfully',
            'data' => $purchases->map(function ($purchase) use ($purchasesItem) {
                // Always return items as an array, not associative
                $items = $purchasesItem->where('purchase_id', $purchase->id)->values()->map(function ($item) {
                    return [
                        'product_id' => $item->productId ? $item->productId->id : null,
                        'product_name' => $item->productId ? $item->productId->name : null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->total,
                    ];
                })->values()->all();

                return [
                    'id' => $purchase->id,
                    'supplier' => $purchase->supplierId ? $purchase->supplierId->name : null,
                    'date' => $purchase->created_at->format('Y-m-d'),
                    'total' => $purchase->total_amount,
                    'items' => $items,
                ];
            }),
        ]);
    }

    public function getReportPenjualan(Request $request)
    {
        $searchDate = $request->query('date', '');
        $searchCustomer = $request->query('customer', '');

        $salesQuery = Sales::with(['customerId']);

        if (!empty($searchDate)) {
            $salesQuery->whereDate('created_at', $searchDate);
        }

        if (!empty($searchCustomer)) {
            $salesQuery->whereHas('customerId', function ($q) use ($searchCustomer) {
                $q->where('name', 'like', '%' . $searchCustomer . '%');
            });
        }

        $sales = $salesQuery->orderBy('created_at', 'desc')->get();
        $salesItems = SalesItem::with(['salesId', 'productId'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Sales report retrieved successfully',
            'data' => $sales->map(function ($sale) use ($salesItems) {
                // Always return items as an array, not associative
                $items = $salesItems->where('sales_id', $sale->id)->values()->map(function ($item) {
                    return [
                        'product_id' => $item->productId ? $item->productId->id : null,
                        'product_name' => $item->productId ? $item->productId->name : null,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                    ];
                })->values()->all();

                return [
                    'id' => $sale->id,
                    'customer' => $sale->customerId ? $sale->customerId->name : null,
                    'date' => $sale->created_at->format('Y-m-d'),
                    'total' => $sale->sales_amount,
                    'items' => $items,
                ];
            }),
        ]);
    }

    public function getReportEmployee(Request $request)
    {
        $searchDate = $request->query('join_date', '');
        $searchEmployee = $request->query('employee', '');

        $employeeQuery = Employees::query();

        if (!empty($searchDate)) {
            $employeeQuery->whereDate('join_date', $searchDate);
        }
        if (!empty($searchEmployee)) {
            $employeeQuery->where('name', 'like', '%' . $searchEmployee . '%');
        }

        $employees = $employeeQuery->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Employee report retrieved successfully',
            'data' => $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'position' => $employee->position,
                    'salary' => $employee->salary,
                    'join_date' => $employee->join_date,
                ];
            }),
        ]);
    }

    public function getReportAttendance(Request $request)
    {
        $searchDate = $request->query('date', '');
        $searchName = $request->query('name', '');

        $query = Attendances::with('employeeId');

        if (!empty($searchDate)) {
            $query->whereDate('date', $searchDate);
        }

        if (!empty($searchName)) {
            $query->whereHas('employeeId', function ($q) use ($searchName) {
                $q->where('name', 'like', '%' . $searchName . '%');
            });
        }

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        // Fetch attendances with pagination
        $attendances = $query->orderBy('date', 'desc')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Attendance report retrieved successfully',
            'data' => $attendances,
        ]);
    }

    public function getReportPayroll(Request $request)
    {
        // Fetch all payroll records with optional search and pagination
        $payrolls = Payrolls::with('employeeId');

        if ($request->has('employee_id')) {
            $payrolls->where('employee_id', $request->input('employee_id'));
        }

        // Add search by employee name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $payrolls->whereHas('employeeId', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // Add search by paid_at date
        if ($request->filled('paid_at')) {
            $paidAt = $request->input('paid_at');
            $payrolls->whereDate('paid_at', $paidAt);
        }

        $payrolls = $payrolls->paginate(10);

        // Return the payroll records as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Payroll records retrieved successfully',
            'data' => $payrolls,
        ], 200);
    }

    public function getReportStockMovement(Request $request)
    {
        // Fetch all stock movements with optional search and pagination
        $stockMovements = StockMovement::with(['productId', 'purchaseItemId', 'salesItemId']);

        if ($request->has('product_id')) {
            $stockMovements->where('product_id', $request->input('product_id'));
        }

        // Add search by product name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $stockMovements->whereHas('productId', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // Add search by type
        if ($request->filled('type')) {
            $type = $request->input('type');
            $stockMovements->where('type', $type);
        }

        // Add search by date
        if ($request->filled('date')) {
            $date = $request->input('date');
            $stockMovements->whereDate('created_at', $date);
        }

        $stockMovements = $stockMovements->orderBy('created_at', 'desc')->paginate(10);

        // Format the response data
        $data = $stockMovements->getCollection()->map(function ($movement) {
            return [
                'id' => $movement->id,
                'product_id' => $movement->product_id,
                'product_name' => $movement->productId ? $movement->productId->name : null,
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'note' => $movement->note,
                'purchase_item_id' => $movement->purchase_item_id,
                'sales_item_id' => $movement->sales_item_id,
                'created_at' => $movement->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Return the stock movements as a JSON response with pagination meta
        return response()->json([
            'status' => true,
            'message' => 'Stock movements retrieved successfully',
            'data' => $data,
            'meta' => [
                'current_page' => $stockMovements->currentPage(),
                'last_page' => $stockMovements->lastPage(),
                'per_page' => $stockMovements->perPage(),
                'total' => $stockMovements->total(),
            ],
        ], 200);
    }
}
