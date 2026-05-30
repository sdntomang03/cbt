<?php

namespace App\Http\Controllers;

use App\Models\Exam;

class SitemapController extends Controller
{
    public function index()
    {
        // Ambil semua ujian yang berstatus publik untuk dimasukkan ke sitemap
        $publicExams = Exam::where('is_public', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Kembalikan view sitemap.blade.php dengan header XML
        return response()
            ->view('sitemap', compact('publicExams'))
            ->header('Content-Type', 'text/xml');
    }
}
