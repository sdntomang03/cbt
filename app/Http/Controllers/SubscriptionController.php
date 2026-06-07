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
        // Panggil dari config, BUKAN dari env()
        // Config::$serverKey = config('services.midtrans.server_key');
        $serverKey = 'Mid-server-itxrFlH-Q5M2mDIcnYcgyZHa';
        Config::$isProduction = config('services.midtrans.is_production');
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

        // 1. CARI SERVER KEY (Coba dari config, jika gagal coba dari env)
        $serverKey = config('services.midtrans.server_key') ?? env('MIDTRANS_SERVER_KEY');

        // 🔥 DETEKTOR ERROR: Jika masih kosong, hentikan dan beri tahu Flutter!
        if (empty($serverKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'SERVER KEY KOSONG! Laravel gagal membaca file .env Anda. Pastikan MIDTRANS_SERVER_KEY sudah ditulis dengan benar di .env',
            ], 500);
        }

        // 2. TERAPKAN KONFIGURASI TEPAT SEBELUM MEMANGGIL MIDTRANS
        Config::$serverKey = $serverKey;
        Config::$isProduction = false; // Paksa ke false karena kita pakai Sandbox
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $user = auth()->user();
        $orderId = 'PRO-'.time().'-'.$user->id;

        // Simpan transaksi
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'plan_name' => $request->plan_name,
            'amount' => $request->amount,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email ?? 'no-email@test.com',
            ],
            // 🔥 TAMBAHKAN BLOK CALLBACKS INI
            'callbacks' => [
                'finish' => 'sahabatkreasianak://payment/success',
                'unfinish' => 'sahabatkreasianak://payment/unfinish',
                'error' => 'sahabatkreasianak://payment/error',
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
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat tagihan Midtrans: '.$e->getMessage(),
            ], 500);
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
