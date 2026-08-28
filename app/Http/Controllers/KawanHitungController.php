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
        // 1. Validasi: Pastikan 'mode' wajib diisi
        $request->validate([
            'operasi' => 'required|in:+,-,*,/',
            'digit1' => 'required|integer|min:1|max:4',
            'digit2' => 'required|integer|min:1|max:4',
            'mode' => 'required|in:belajar,latihan',
        ]);

        $mode = $request->mode;

        // 2. Jika mode 'belajar', paksa soal cuma 1
        $jumlahSoal = ($mode === 'belajar') ? 1 : ($request->jumlah_soal ?? 5);

        $soal = [];
        for ($i = 0; $i < $jumlahSoal; $i++) {

            $min1 = pow(10, $request->digit1 - 1);
            $max1 = pow(10, $request->digit1) - 1;
            if ($request->digit1 == 1) {
                $min1 = 1;
            }

            $min2 = pow(10, $request->digit2 - 1);
            $max2 = pow(10, $request->digit2) - 1;
            if ($request->digit2 == 1) {
                $min2 = 1;
            }

            if ($request->operasi === '/') {
                if ($request->digit2 == 1) {
                    $min2 = 2;
                }
                $n2 = rand($min2, $max2);

                $minMultiplier = (int) ceil($min1 / $n2);
                $maxMultiplier = (int) floor($max1 / $n2);

                if ($minMultiplier > $maxMultiplier) {
                    $n1 = $n2;
                } else {
                    $multiplier = rand($minMultiplier, $maxMultiplier);
                    $n1 = $n2 * $multiplier;
                }
            } else {
                $n1 = rand($min1, $max1);
                $n2 = rand($min2, $max2);

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
        session(['config_hitung' => $request->all()]);

        // 3. LOGIKA ARAH HALAMAN (REDIRECT)
        if ($mode === 'belajar') {
            return redirect()->route('hitung.belajar');
        }

        return redirect()->route('hitung.latihan');
    }

    // Menampilkan Halaman Belajar (1 Soal)
    public function belajar()
    {
        $soal = session('soal_hitung');
        $config = session('config_hitung');

        if (! $soal || ! isset($soal[0])) {
            return redirect()->route('hitung.index');
        }

        $s = $soal[0];

        return view('kawan-hitung.belajar', compact('s', 'config'));
    }

    // Menampilkan Halaman Latihan (Banyak Soal)
    public function latihan()
    {
        $soal = session('soal_hitung');
        if (! $soal) {
            return redirect()->route('hitung.index');
        }

        return view('kawan-hitung.latihan', compact('soal'));
    }

    // Menghitung Nilai Akhir Latihan
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

            $jawab_mentah = isset($jawaban_user[$i]) ? str_replace('.', '', $jawaban_user[$i]) : null;
            $jawab = ($jawab_mentah !== null && $jawab_mentah !== '') ? floatval($jawab_mentah) : null;
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
