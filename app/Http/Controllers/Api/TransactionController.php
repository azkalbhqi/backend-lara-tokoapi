<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function store(App\Http\Requests\TransactionRequest $request)
    {
        $user = auth('sanctum')->user() ?? auth()->user();
        
        // Use user info if logged in, otherwise use request info
        $customerName = $user ? $user->name : $request->customer_name;
        $customerEmail = $user ? $user->email : $request->customer_email;

        if (!$customerName || !$customerEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Informasi pembeli (nama & email) diperlukan'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($request, $user, $customerName, $customerEmail) {
                $totalPrice = 0;
                $itemsToCreate = [];

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok produk '{$product->name}' tidak mencukupi");
                    }

                    $itemTotalPrice = $product->price * $item['quantity'];
                    $totalPrice += $itemTotalPrice;

                    $itemsToCreate[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'total_price' => $itemTotalPrice,
                    ];

                    // Update stock
                    $product->decrement('stock', $item['quantity']);
                }

                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user ? $user->id : null,
                    'total_price' => $totalPrice,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                ]);

                // Create items
                foreach ($itemsToCreate as $itemData) {
                    $transaction->items()->create($itemData);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil',
                    'data' => $transaction->load('items.product')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function simulate(App\Http\Requests\SimulateRequest $request)
    {
        $totalPrice = 0;
        $items = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            $itemTotalPrice = $product->price * $item['quantity'];
            $totalPrice += $itemTotalPrice;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'price' => (float)$product->price,
                'total_price' => (float)$itemTotalPrice,
                'available_stock' => $product->stock,
                'is_sufficient' => $product->stock >= $item['quantity']
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulasi perhitungan berhasil',
            'data' => [
                'items' => $items,
                'total_price' => $totalPrice,
            ]
        ]);
    }

    public function index()
    {
        $transactions = Transaction::with(['items.product', 'user'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
