<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class TransactionController extends Controller
{
    #[OA\Get(
        path: '/api/transactions',
        summary: 'Get all transactions',
        tags: ['Transactions'],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation')
        ]
    )]
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $transactions = Transaction::with(['items.product', 'user'])->latest()->get();
            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        }

        $transactions = Transaction::with(['items.product', 'user'])->latest()->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    #[OA\Post(
        path: '/api/transactions/simulate',
        summary: 'Simulate a transaction',
        tags: ['Transactions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer'),
                                new OA\Property(property: 'quantity', type: 'integer')
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Simulation result')
        ]
    )]
    public function simulate(Request $request)
    {
        if ($request->wantsJson() || $request->isMethod('post')) {
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

        $products = Product::where('stock', '>', 0)->get();
        return view('transactions.simulate', compact('products'));
    }

    #[OA\Post(
        path: '/api/transactions',
        summary: 'Create a new transaction',
        security: [['bearerAuth' => []]],
        tags: ['Transactions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['items'],
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'product_id', type: 'integer'),
                                new OA\Property(property: 'quantity', type: 'integer')
                            ]
                        )
                    ),
                    new OA\Property(property: 'customer_name', type: 'string', nullable: true),
                    new OA\Property(property: 'customer_email', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Transaction created successfully'),
            new OA\Response(response: 400, description: 'Bad request (e.g., insufficient stock)'),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]




    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string',
            'customer_email' => 'nullable|email',
        ]);

        $user = auth('sanctum')->user() ?? auth()->user();
        
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

                    $product->decrement('stock', $item['quantity']);
                }

                $transaction = Transaction::create([
                    'user_id' => $user ? $user->id : null,
                    'total_price' => $totalPrice,
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                ]);

                foreach ($itemsToCreate as $itemData) {
                    $transaction->items()->create($itemData);
                }

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Transaksi berhasil',
                        'data' => $transaction->load('items.product')
                    ], 201);
                }

                return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil.');
            });
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
