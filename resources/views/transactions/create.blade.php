@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Menu Selection -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Menu</h2>

                    @foreach ($menus as $category => $categoryMenus)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-700 mb-3">{{ $category }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($categoryMenus as $menu)
                                    <div class="bg-gray-50 rounded-lg p-4 cursor-pointer hover:bg-gray-100"
                                        onclick="addToCart({{ json_encode($menu) }})">
                                        <div class="flex items-center">
                                            @if ($menu->image)
                                                <img src="{{ asset($menu->image) }}" alt="{{ $menu->name }}"
                                                    class="w-16 h-16 object-cover rounded">
                                            @else
                                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                                    <span class="text-gray-500 text-xs">No Image</span>
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <h4 class="font-semibold text-gray-800">{{ $menu->name }}</h4>
                                                <p class="text-green-600 font-medium">Rp
                                                    {{ number_format($menu->price, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cart -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Pesanan</h2>

                    <div id="cart-items" class="space-y-4 mb-4">
                        <!-- Cart items will be dynamically added here -->
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span class="font-semibold">Total:</span>
                            <span id="total-amount" class="font-bold">Rp 0</span>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Metode Pembayaran</label>
                            <select id="payment-method" class="w-full border rounded-lg px-3 py-2">
                                <option value="cash">Cash</option>
                                <option value="debit">Debit</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah Bayar</label>
                            <input type="number" id="paid-amount" class="w-full border rounded-lg px-3 py-2"
                                onchange="calculateChange()">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kembalian</label>
                            <input type="text" id="change-amount" class="w-full border rounded-lg px-3 py-2" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Catatan</label>
                            <textarea id="notes" class="w-full border rounded-lg px-3 py-2" rows="2"></textarea>
                        </div>

                        <button onclick="processTransaction()"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                            Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let cart = [];

            function addToCart(menu) {
                console.log(menu); // Cek output menu
                const existingItem = cart.find(item => item.menu_id === menu.id);

                if (existingItem) {
                    existingItem.quantity += 1;
                    existingItem.subtotal = existingItem.quantity * menu.price;
                } else {
                    cart.push({
                        menu_id: menu.id,
                        name: menu.name,
                        price: menu.price,
                        quantity: 1,
                        subtotal: menu.price
                    });
                }

                updateCartDisplay();
            }


            function updateQuantity(index, delta) {
                cart[index].quantity += delta;

                if (cart[index].quantity < 1) {
                    cart.splice(index, 1);
                } else {
                    cart[index].subtotal = cart[index].quantity * cart[index].price;
                }

                console.log(cart); // Cek cart setelah perubahan
                updateCartDisplay();
            }


            function updateCartDisplay() {
                const cartContainer = document.getElementById('cart-items');
                cartContainer.innerHTML = '';

                let total = 0;

                cart.forEach((item, index) => {
                    total += item.subtotal;

                    cartContainer.innerHTML += `
            <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                <div>
                    <h4 class="font-semibold">${item.name}</h4>
                    <p class="text-sm text-gray-600">Rp ${numberFormat(item.price)} x ${item.quantity}</p>
                    <p class="font-medium text-green-600">Rp ${numberFormat(item.subtotal)}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="updateQuantity(${index}, -1)" 
                            class="bg-red-100 text-red-600 p-1 rounded hover:bg-red-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </button>
                    <span class="font-medium">${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)"
                            class="bg-green-100 text-green-600 p-1 rounded hover:bg-green-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
                });

                document.getElementById('total-amount').textContent = `Rp ${numberFormat(total)}`;
                calculateChange();
            }

            function calculateChange() {
                const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
                const paidAmount = parseFloat(document.getElementById('paid-amount').value) || 0;
                const change = paidAmount - total;

                console.log('Total:', total); // Cek total
                console.log('Paid:', paidAmount); // Cek paid amount
                document.getElementById('change-amount').value = `Rp ${numberFormat(Math.max(0, change))}`;
            }


            function numberFormat(number) {
                return number.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function processTransaction() {
                if (cart.length === 0) {
                    alert('Silakan pilih menu terlebih dahulu');
                    return;
                }

                const paidAmount = parseFloat(document.getElementById('paid-amount').value);
                const total = cart.reduce((sum, item) => sum + item.subtotal, 0);

                if (!paidAmount || paidAmount < total) {
                    alert('Jumlah pembayaran tidak mencukupi');
                    return;
                }

                const transaction = {
                    items: cart.map(item => ({
                        menu_id: item.menu_id,
                        quantity: item.quantity
                    })),
                    paid_amount: paidAmount,
                    payment_method: document.getElementById('payment-method').value,
                    notes: document.getElementById('notes').value
                };

                console.log(transaction); // Cek apakah data transaksi sudah benar

                // Kirim data transaksi ke server
                fetch('/transactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(transaction)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Transaksi berhasil');
                            window.open(`/transactions/${data.transaction.id}/print`, '_blank');
                            cart = [];
                            updateCartDisplay();
                            document.getElementById('paid-amount').value = '';
                            document.getElementById('notes').value = '';
                        } else {
                            alert(data.message || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan sistem');
                    });
            }
        </script>
    @endpush
@endsection
