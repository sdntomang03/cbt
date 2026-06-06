<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Module;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    // ========================================================
    // SISI GURU / ADMIN (CRUD)
    // ========================================================

    public function index()
    {
        $modules = Module::with(['subject', 'level', 'author'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.modules.index', compact('modules'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $levels = Level::all();

        return view('admin.modules.form', compact('subjects', 'levels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'level_id' => 'required|exists:levels,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:2048',
            'document_path' => 'nullable|mimes:pdf|max:5120',
            'status' => 'required|in:draft,published,archived',
            'is_public' => 'boolean',
            'is_premium' => 'boolean',
            'estimated_time_minutes' => 'integer|min:1',
            'reward_points' => 'integer|min:0',
        ]);

        $validated['slug'] = Str::slug($request->title).'-'.Str::random(5);
        $validated['author_id'] = auth()->id();
        $validated['is_public'] = $request->has('is_public');
        $validated['is_premium'] = $request->has('is_premium');

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('modules/thumbnails', 'public');
        }
        if ($request->hasFile('document_path')) {
            $validated['document_path'] = $request->file('document_path')->store('modules/documents', 'public');
        }

        Module::create($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Modul berhasil ditambahkan!');
    }

    public function edit(Module $module)
    {
        $subjects = Subject::all();
        $levels = Level::all();

        return view('admin.modules.form', compact('module', 'subjects', 'levels'));
    }

    public function update(Request $request, Module $module)
    {
        // Validasi mirip dengan store()...
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'level_id' => 'required|exists:levels,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url',
            'thumbnail' => 'nullable|image|max:2048',
            'document_path' => 'nullable|mimes:pdf|max:5120',
            'status' => 'required|in:draft,published,archived',
            'estimated_time_minutes' => 'integer|min:1',
            'reward_points' => 'integer|min:0',
        ]);

        $validated['is_public'] = $request->has('is_public');
        $validated['is_premium'] = $request->has('is_premium');

        if ($request->hasFile('thumbnail')) {
            if ($module->thumbnail) {
                Storage::disk('public')->delete($module->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('modules/thumbnails', 'public');
        }

        if ($request->hasFile('document_path')) {
            if ($module->document_path) {
                Storage::disk('public')->delete($module->document_path);
            }
            $validated['document_path'] = $request->file('document_path')->store('modules/documents', 'public');
        }

        $module->update($validated);

        return redirect()->route('admin.modules.index')->with('success', 'Modul berhasil diperbarui!');
    }

    public function destroy(Module $module)
    {
        if ($module->thumbnail) {
            Storage::disk('public')->delete($module->thumbnail);
        }
        if ($module->document_path) {
            Storage::disk('public')->delete($module->document_path);
        }

        $module->delete();

        return redirect()->back()->with('success', 'Modul berhasil dihapus!');
    }

    // ========================================================
    // SISI SISWA / PUBLIK
    // ========================================================

    public function studentIndex(Request $request)
    {
        $modules = Module::where('status', 'published')
            ->where('is_public', true)
            ->with(['subject', 'level'])
            ->latest()
            ->paginate(12);

        return view('public.modules.index', compact('modules'));
    }

    public function studentShow($slug)
    {
        $module = Module::where('slug', $slug)->where('status', 'published')->firstOrFail();

        // Cegatan Premium (Sama seperti CBT)
        if ($module->is_premium) {
            if (! auth()->check() || empty(auth()->user()->premium_until) || Carbon::parse(auth()->user()->premium_until)->isPast()) {
                return redirect()->route('public.modules.index')
                    ->with('error', 'Akses ditolak. Modul ini eksklusif untuk member Premium.');
            }
        }

        // Tambah jumlah view
        $module->increment('view_count');

        return view('public.modules.show', compact('module'));
    }
}
