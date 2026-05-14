<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:sanctum,web', 'verified'])->name('dashboard');

// Public routes for API access (previously in api.php)
Route::get('/api-products', [ProductController::class, 'index']); // Optional: if they specifically hit /products they'll hit the resource. Let's just use the resource.

Route::middleware('auth:sanctum,web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('products', ProductController::class);
    
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::match(['get', 'post'], '/transactions/simulate', [TransactionController::class, 'simulate'])->name('transactions.simulate');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

require __DIR__.'/auth.php';
