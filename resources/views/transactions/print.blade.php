<!DOCTYPE html>
<html>

<head>
    <title>Struk Pembayaran - {{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            width: 80mm;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .item {
            margin-bottom: 5px;
        }

        .total {
            border-top: 1px dashed #000;
            margin-top: 10px;
            padding-top: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0;">{{ config('app.name') }}</h2>
        <p style="margin: 5px 0;">{{ $transaction->invoice_number }}</p>
        <p style="margin: 5px 0;">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="items">
        @foreach ($transaction->items as $item)
            <div class="item">
                <div>{{ $item->menu->name }}</div>
                <div>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</div>
                <div style="text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
        @endforeach
    </div>

    <div class="total">
        <div style="display: flex; justify-content: space-between;">
            <span>Total</span>
            <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Bayar</span>
            <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Kembali</span>
            <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih atas kunjungan Anda</p>
        <p>Silakan datang kembali</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
