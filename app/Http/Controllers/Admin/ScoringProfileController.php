<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\ScoringProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScoringProfileController extends Controller
{
    public function index()
    {
        $profiles = $this->visibleProfiles()
            ->with('school')
            ->orderBy('name')
            ->get();

        $schools = auth()->user()->hasRole('admin')
            ? School::orderBy('name')->get()
            : collect();

        return view('admin.scoring-profiles.index', compact('profiles', 'schools'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['school_id'] = $this->schoolId($request);
        $data['rules'] = $this->rules($request);

        DB::transaction(function () use ($data) {
            ScoringProfile::create([
                'school_id' => $data['school_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'rules' => $data['rules'],
            ]);
        });

        return redirect()->route('admin.scoring-profiles.index')
            ->with('success', 'Profil scoring berhasil ditambahkan.');
    }

    public function update(Request $request, ScoringProfile $scoringProfile)
    {
        $this->ensureVisible($scoringProfile);

        $data = $this->validated($request, $scoringProfile);
        $data['school_id'] = $this->schoolId($request, $scoringProfile);
        $data['rules'] = $this->rules($request);

        DB::transaction(function () use ($scoringProfile, $data) {
            $scoringProfile->update([
                'school_id' => $data['school_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'rules' => $data['rules'],
            ]);
        });

        return redirect()->route('admin.scoring-profiles.index')
            ->with('success', 'Profil scoring berhasil diperbarui.');
    }

    public function destroy(ScoringProfile $scoringProfile)
    {
        $this->ensureVisible($scoringProfile);
        $scoringProfile->delete();

        return redirect()->route('admin.scoring-profiles.index')
            ->with('success', 'Profil scoring berhasil dihapus.');
    }

    private function visibleProfiles()
    {
        $user = auth()->user();
        $query = ScoringProfile::query();

        if (! $user->hasRole('admin')) {
            $query->where(function ($profileQuery) use ($user) {
                $profileQuery->whereNull('school_id')->orWhere('school_id', $user->school_id);
            });
        }

        return $query;
    }

    private function ensureVisible(ScoringProfile $profile): void
    {
        abort_unless($this->visibleProfiles()->whereKey($profile->id)->exists(), 404);
    }

    private function validated(Request $request, ?ScoringProfile $profile = null): array
    {
        $uniqueCode = Rule::unique('scoring_profiles', 'code');
        if ($profile) {
            $uniqueCode->ignore($profile->id);
        }

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $uniqueCode],
            'name' => ['required', 'string', 'max:255'],
            'scoring_type' => ['required', Rule::in(['standard', 'weighted'])],
            'result_mode' => ['required', Rule::in(['average', 'total'])],
            'correct' => ['required', 'numeric', 'min:0'],
            'wrong' => ['required', 'numeric'],
            'empty' => ['required', 'numeric'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);
    }

    private function rules(Request $request): array
    {
        return [
            'type' => $request->input('scoring_type'),
            'result_mode' => $request->input('result_mode'),
            'correct' => (float) $request->input('correct'),
            'wrong' => (float) $request->input('wrong'),
            'empty' => (float) $request->input('empty'),
        ];
    }

    private function schoolId(Request $request, ?ScoringProfile $profile = null): ?int
    {
        if (auth()->user()->hasRole('admin')) {
            return $request->filled('school_id') ? (int) $request->input('school_id') : null;
        }

        return auth()->user()->school_id ?? $profile?->school_id;
    }
}
