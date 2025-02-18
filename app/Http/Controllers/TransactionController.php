<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('items.menu')
            ->latest()
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $menus = Menu::where('is_available', true)
            ->with('category')
            ->get()
            ->groupBy('category.name');

        return view('transactions.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,debit,qris',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];
                $totalAmount += $subtotal;
            }

            // Create transaction
            $transaction = Transaction::create([
                'invoice_number' => 'INV' . date('YmdHis'),
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->paid_amount - $totalAmount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes
            ]);

            // Create transaction items
            foreach ($request->items as $item) {
                $menu = Menu::findOrFail($item['menu_id']);
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $menu->price,
                    'subtotal' => $menu->price * $item['quantity']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'transaction' => $transaction->load('items.menu')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('items.menu');
        return view('transactions.show', compact('transaction'));
    }

    public function print(Transaction $transaction)
    {
        $transaction->load('items.menu');
        return view('transactions.print', compact('transaction'));
    }
}
