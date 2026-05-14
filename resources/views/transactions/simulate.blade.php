<x-app-layout>
    <x-slot name="header" class="flex">
        <a href="/transactions">
           Back
        </a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Simulasi Transaksi (Beli Produk)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Product Selection -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-lg mb-4">Pilih Produk</h3>
                        <div class="mb-4">
                            <x-input-label for="product_id" :value="__('Produk')" />
                            <select id="product_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                                        {{ $product->name }} (Stok: {{ $product->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <x-input-label for="quantity" :value="__('Jumlah')" />
                            <x-text-input id="quantity" class="block mt-1 w-full" type="number" min="1" value="1" />
                        </div>
                        <x-primary-button id="add-to-cart" class="w-full justify-center">
                            Tambah ke Keranjang
                        </x-primary-button>
                    </div>
                </div>

                <!-- Cart & Checkout -->
                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div id="alert-container"></div>
                        <h3 class="font-bold text-lg mb-4">Keranjang Belanja</h3>
                        
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200 border" id="cart-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200" id="cart-items">
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Keranjang masih kosong</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50 font-bold">
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right">Grand Total:</td>
                                        <td class="px-4 py-2 text-indigo-600" id="grand-total">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <form id="transaction-form">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <x-input-label for="customer_name" :value="__('Nama Pembeli')" />
                                    <x-text-input id="customer_name" class="block mt-1 w-full bg-gray-50" type="text" name="customer_name" value="{{ auth()->user()->name }}" readonly required />
                                </div>
                                <div>
                                    <x-input-label for="customer_email" :value="__('Email Pembeli')" />
                                    <x-text-input id="customer_email" class="block mt-1 w-full bg-gray-50" type="email" name="customer_email" value="{{ auth()->user()->email }}" readonly required />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md mr-4" href="{{ route('transactions.index') }}">
                                    {{ __('Batal') }}
                                </a>
                                <x-primary-button type="submit" id="submit-button" disabled>
                                    {{ __('Checkout Sekarang') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartItems = [];
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const addToCartBtn = document.getElementById('add-to-cart');
            const cartItemsBody = document.getElementById('cart-items');
            const grandTotalDisplay = document.getElementById('grand-total');
            const submitButton = document.getElementById('submit-button');
            const form = document.getElementById('transaction-form');
            const alertContainer = document.getElementById('alert-container');

            function formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
            }

            function updateCartUI() {
                if (cartItems.length === 0) {
                    cartItemsBody.innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Keranjang masih kosong</td></tr>';
                    grandTotalDisplay.textContent = 'Rp 0';
                    submitButton.disabled = true;
                    return;
                }

                submitButton.disabled = false;
                cartItemsBody.innerHTML = '';
                let grandTotal = 0;

                cartItems.forEach((item, index) => {
                    const row = document.createElement('tr');
                    const total = item.price * item.quantity;
                    grandTotal += total;

                    row.innerHTML = `
                        <td class="px-4 py-2 text-sm">${item.name}</td>
                        <td class="px-4 py-2 text-sm">${formatRupiah(item.price)}</td>
                        <td class="px-4 py-2 text-sm">${item.quantity}</td>
                        <td class="px-4 py-2 text-sm font-medium">${formatRupiah(total)}</td>
                        <td class="px-4 py-2 text-right">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-item" data-index="${index}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </td>
                    `;
                    cartItemsBody.appendChild(row);
                });

                grandTotalDisplay.textContent = formatRupiah(grandTotal);

                document.querySelectorAll('.remove-item').forEach(btn => {
                    btn.addEventListener('click', function() {
                        cartItems.splice(this.dataset.index, 1);
                        updateCartUI();
                    });
                });
            }

            addToCartBtn.addEventListener('click', function() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                if (!selectedOption.value) return;

                const productId = selectedOption.value;
                const productName = selectedOption.dataset.name;
                const price = parseFloat(selectedOption.dataset.price);
                const quantity = parseInt(quantityInput.value);
                const stock = parseInt(selectedOption.dataset.stock);

                // Check if already in cart
                const existingItem = cartItems.find(item => item.productId === productId);
                const totalQty = (existingItem ? existingItem.quantity : 0) + quantity;

                if (totalQty > stock) {
                    alert('Stok tidak mencukupi!');
                    return;
                }

                if (existingItem) {
                    existingItem.quantity = totalQty;
                } else {
                    cartItems.push({ productId, name: productName, price, quantity });
                }

                updateCartUI();
                productSelect.value = '';
                quantityInput.value = 1;
            });

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitOriginalText = submitButton.textContent;
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses Checkout...';
                alertContainer.innerHTML = '';

                const data = {
                    items: cartItems.map(item => ({
                        product_id: item.productId,
                        quantity: item.quantity
                    })),
                    customer_name: document.getElementById('customer_name').value,
                    customer_email: document.getElementById('customer_email').value
                };

                try {
                    const response = await fetch('/api/transactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok) {
                        alertContainer.innerHTML = `
                            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">Transaksi Berhasil! Mengalihkan...</span>
                            </div>
                        `;
                        setTimeout(() => {
                            window.location.href = '{{ route("transactions.index") }}';
                        }, 1500);
                    } else {
                        alertContainer.innerHTML = `
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">${result.message || 'Terjadi kesalahan'}</span>
                            </div>
                        `;
                        submitButton.disabled = false;
                        submitButton.textContent = submitOriginalText;
                    }
                } catch (error) {
                    alertContainer.innerHTML = `
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">Terjadi kesalahan koneksi</span>
                        </div>
                    `;
                    submitButton.disabled = false;
                    submitButton.textContent = submitOriginalText;
                }
            });
        });
    </script>
</x-app-layout>
