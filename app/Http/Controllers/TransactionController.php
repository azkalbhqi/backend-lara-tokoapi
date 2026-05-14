<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['items.product', 'user'])->latest()->paginate(10);
        return view('transactions.index', compact('transactions'));
    }

    public function simulate()
    {
        $products = Product::where('stock', '>', 0)->get();
        return view('transactions.simulate', compact('products'));
    }
}
