<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // 1. Fungsi untuk dipanggil dari Flutter (Membuat Tagihan)
    public function checkout(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string',
            'amount' => 'required|integer',
        ]);

        $user = auth()->user();
        $orderId = 'PRO-'.time().'-'.$user->id; // ID Unik

        // Simpan transaksi berstatus Pending
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'plan_name' => $request->plan_name,
            'amount' => $request->amount,
        ]);

        // Minta Snap Token ke Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email ?? 'no-email@test.com',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $snapUrl = Snap::getSnapUrl($params);

            $transaction->update(['snap_token' => $snapToken]);

            return response()->json([
                'status' => 'success',
                'redirect_url' => $snapUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Fungsi Webhook (Menerima konfirmasi pembayaran sukses dari Midtrans)
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;

        $transaction = Transaction::where('order_id', $orderId)->first();
        if (! $transaction) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            // Jika LUNAS
            $transaction->update(['status' => 'success']);

            $user = User::find($transaction->user_id);

            // Tentukan durasi berdasarkan nama paket
            $months = 1;
            if (str_contains($transaction->plan_name, '6 Bulan')) {
                $months = 6;
            }
            if (str_contains($transaction->plan_name, 'Seumur Hidup')) {
                $months = 1200;
            } // 100 tahun

            // Tambah waktu Premium
            $newDate = ($user->is_premium && $user->premium_until)
                ? Carbon::parse($user->premium_until)->addMonths($months)
                : now()->addMonths($months);

            $user->update([
                'is_premium' => true,
                'premium_until' => $newDate,
            ]);
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $transaction->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }
}
