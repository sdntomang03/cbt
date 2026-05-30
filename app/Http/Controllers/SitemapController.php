<?php

namespace App\Http\Controllers;

use App\Models\Exam;

class SitemapController extends Controller
{
    public function index()
    {
        $publicExams = Exam::where('is_public', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()
            ->view('sitemap', compact('publicExams'))
            ->header('Content-Type', 'text/xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate') // Mencegah caching
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
