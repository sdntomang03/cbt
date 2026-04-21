<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\RegistrationSetting;
use App\Models\School;
use Illuminate\Http\Request;

class RegistrationSettingController extends Controller
{
    public function edit(Request $request)
    {
        $schools = School::orderBy('name', 'asc')->get();
        $selectedSchoolId = $request->query('school_id', auth()->user()->school_id);
        
        if (!$selectedSchoolId && $schools->isNotEmpty()) {
            $selectedSchoolId = $schools->first()->id;
        }

        $setting = null;
        $examSessions = collect(); 

        if ($selectedSchoolId) {
            // Ambil data setting sekolah ini (bisa null jika belum pernah di-setting)
            $setting = RegistrationSetting::where('school_id', $selectedSchoolId)->first();

            $examSessions = ExamSession::with('exam')
                ->where('school_id', $selectedSchoolId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('admin.settings.registration', compact('schools', 'selectedSchoolId', 'setting', 'examSessions'));
    }

  public function update(Request $request)
    {
        $request->validate([
            'school_id'                  => 'required|exists:schools,id',
            'default_exam_session_ids'   => 'nullable|array',
            'default_exam_session_ids.*' => 'exists:exam_sessions,id',
        ]);

        // Simpan atau Perbarui pengaturan menggunakan updateOrCreate
        RegistrationSetting::updateOrCreate(
            ['school_id' => $request->school_id],
            // Jika kosong, simpan sebagai array kosong []
            ['default_exam_session_ids' => $request->default_exam_session_ids ?? []] 
        );

        return redirect()->route('admin.settings.registration', ['school_id' => $request->school_id])
                         ->with('success', 'Pengaturan sesi default berhasil diperbarui!');
    }
}