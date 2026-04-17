<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KawanHitungController extends Controller
{
    public function index()
    {
        return view('kawan-hitung.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'operasi' => 'required|in:+,-,*,/',
            'digit1' => 'required|integer|min:1|max:4',
            'digit2' => 'required|integer|min:1|max:4',
            'jumlah_soal' => 'required|integer|min:1|max:50',
        ]);

        $soal = [];
        for ($i = 0; $i < $request->jumlah_soal; $i++) {

            // Tentukan batas min & max untuk Angka Pertama
            $min1 = pow(10, $request->digit1 - 1);
            $max1 = pow(10, $request->digit1) - 1;
            if ($request->digit1 == 1) {
                $min1 = 1;
            }

            // Tentukan batas min & max untuk Angka Kedua
            $min2 = pow(10, $request->digit2 - 1);
            $max2 = pow(10, $request->digit2) - 1;
            if ($request->digit2 == 1) {
                $min2 = 1;
            }

            if ($request->operasi === '/') {
                // LOGIKA PEMBAGIAN FLEKSIBEL & HABIS DIBAGI
                if ($request->digit2 == 1) {
                    $min2 = 2;
                } // Hindari bagi 1 agar soal tidak terlalu mudah
                $n2 = rand($min2, $max2);

                // Cari pengali agar n1 masuk ke dalam range digit1
                $minMultiplier = (int) ceil($min1 / $n2);
                $maxMultiplier = (int) floor($max1 / $n2);

                // Jika user iseng memasukkan 1 digit dibagi 3 digit, kita amankan agar tidak error
                if ($minMultiplier > $maxMultiplier) {
                    $n1 = $n2;
                } else {
                    $multiplier = rand($minMultiplier, $maxMultiplier);
                    $n1 = $n2 * $multiplier;
                }
            } else {
                // Operasi Selain Pembagian
                $n1 = rand($min1, $max1);
                $n2 = rand($min2, $max2);

                // Jika pengurangan, pastikan angka pertama lebih besar (kecuali ingin ada nilai minus)
                if ($request->operasi === '-' && $n2 > $n1) {
                    $temp = $n1;
                    $n1 = $n2;
                    $n2 = $temp;
                }
            }

            $soal[] = [
                'id' => $i,
                'n1' => $n1,
                'n2' => $n2,
                'op' => $request->operasi,
            ];
        }

        session(['soal_hitung' => $soal]);

        return redirect()->route('hitung.latihan');
    }

    public function latihan()
    {
        $soal = session('soal_hitung');
        if (! $soal) {
            return redirect()->route('hitung.index');
        }

        return view('kawan-hitung.latihan', compact('soal'));
    }

    public function submit(Request $request)
    {
        $soal = session('soal_hitung');
        if (! $soal) {
            return redirect()->route('hitung.index');
        }

        $jawaban_user = $request->jawaban ?? [];
        $hasil = [];
        $benar = 0;

        foreach ($soal as $i => $s) {
            $n1 = $s['n1'];
            $n2 = $s['n2'];
            $op = $s['op'];

            $kunci = 0;
            match ($op) {
                '+' => $kunci = $n1 + $n2,
                '-' => $kunci = $n1 - $n2,
                '*' => $kunci = $n1 * $n2,
                '/' => $kunci = $n1 / $n2,
            };

            $jawab = isset($jawaban_user[$i]) ? floatval($jawaban_user[$i]) : null;
            $is_correct = ($jawab === floatval($kunci));

            if ($is_correct) {
                $benar++;
            }

            $hasil[] = [
                'soal' => $s,
                'jawaban_user' => $jawab,
                'kunci' => $kunci,
                'is_correct' => $is_correct,
            ];
        }

        $nilai = ($benar / count($soal)) * 100;
        session()->forget('soal_hitung');

        return view('kawan-hitung.hasil', compact('hasil', 'nilai', 'benar'));
    }
}
