<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSection;
use App\Models\ExamSession;
use App\Models\ExamType;
use App\Models\Question;
use App\Models\QuestionMatch;
use App\Models\QuestionOption;
use App\Models\ScoringProfile;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        // ---------------------------------------------------
        // 1. BUAT SCORING PROFILE (BARU)
        // ---------------------------------------------------
        $profileStandard = ScoringProfile::create([
            'school_id' => 1,
            'code' => 'STANDARD_100',
            'name' => 'Standar Benar +1',
            'rules' => json_encode([
                'type' => 'standard',
                'result_mode' => 'average',
                'correct' => 1,
                'wrong' => 0,
                'empty' => 0,
            ]),
        ]);

        $profileTkp = ScoringProfile::create([
            'school_id' => 1,
            'code' => 'TKP_WEIGHTED',
            'name' => 'TKP Bobot 1-5',
            'rules' => json_encode([
                'type' => 'weighted',
                'result_mode' => 'total',
            ]),
        ]);

        $this->command->info('Scoring Profiles created.');

        $sectionTpk = Section::create([
            'abbreviation' => 'TPU',
            'name' => 'Tes Pengetahuan Umum',
        ]);
        $sectionTkpMaster = Section::create([
            'abbreviation' => 'TKP',
            'name' => 'Tes Karakteristik Pribadi',
        ]);

        // ---------------------------------------------------
        // 2. BUAT JENIS UJIAN
        // ---------------------------------------------------
        $examType = ExamType::create([
            'school_id' => 1,
            'name' => 'Penilaian Harian',
        ]);

        // ---------------------------------------------------
        // 3. BUAT UJIAN
        // ---------------------------------------------------
        $title = 'Ujian Simulasi Fitur Lengkap';
        $exam = Exam::create([
            'school_id' => 1,
            'teacher_id' => 1,
            'exam_type_id' => $examType->id,
            'scoring_profile_id' => $profileStandard->id, // Fallback profile
            'title' => $title,
            'slug' => Str::slug($title.'-'.Str::random(5)),
            'duration_minutes' => 60,
            'random_question' => true,
            'random_answer' => true,
            'show_explanation' => true,
            'level_id' => 1,
            'subject_id' => 1,
            'status' => 'published',
        ]);

        $this->command->info('Exam created: '.$exam->title);

        // ---------------------------------------------------
        // 4. BUAT EXAM SECTION (BARU)
        // ---------------------------------------------------
        $sectionMain = ExamSection::create([
            'exam_id' => $exam->id,
            'scoring_profile_id' => $profileStandard->id,
            'section_id' => $sectionTpk->id,
            'order' => 1,
        ]);

        $sectionTkp = ExamSection::create([
            'exam_id' => $exam->id,
            'scoring_profile_id' => $profileTkp->id,
            'section_id' => $sectionTkpMaster->id,
            'order' => 2,
        ]);

        $this->command->info('Exam Sections created.');

        // ---------------------------------------------------
        // 5. BUAT SESI UJIAN
        // ---------------------------------------------------
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'session_name' => 'Sesi Uji Coba - Kelas A',
            'token' => 'ABC123',
            'school_id' => 1,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHours(3),
        ]);

        // ---------------------------------------------------
        // 6. DAFTARKAN SISWA (EXAM ATTEMPTS - PENGGANTI PIVOT)
        // ---------------------------------------------------
        DB::table('exam_attempts')->insert([
            'exam_session_id' => $session->id,
            'user_id' => 3,
            'status' => 'not_started',
            'raw_score' => 0,
            'final_score' => 0,
            'is_locked' => false,
            'violation_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Student attempt created.');

        // ---------------------------------------------------
        // 7. INPUT SOAL-SOAL (DIMASUKKAN KE SECTION MAIN)
        // ---------------------------------------------------

        // Soal 1: Pilihan Ganda
        $q1 = Question::create([
            'exam_section_id' => $sectionMain->id, // MENGGUNAKAN SECTION ID
            'user_id' => 1,
            'type' => 'single_choice',
            'content' => '<p>4+4 = ...</p>',
            'explanation' => '<p>Penjumlahan 4 + 4 menghasilkan 8. Jadi jawaban yang tepat adalah <strong>8</strong>.</p>',
        ]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => '6', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => '8', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => '9', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q1->id, 'option_text' => '12', 'is_correct' => false]);

        // Soal 2: Pilihan Ganda Kompleks
        $q2 = Question::create([
            'exam_section_id' => $sectionMain->id,
            'user_id' => 1,
            'type' => 'complex_choice',
            'content' => '<p>Manakah yang termasuk perangkat <strong>Input</strong> komputer? (Pilih lebih dari satu)</p>',
            'explanation' => '<p>Keyboard dan mouse termasuk perangkat input karena digunakan untuk memasukkan data atau perintah ke komputer. Monitor dan printer termasuk perangkat output.</p>',
        ]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'Monitor', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'Keyboard', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'Mouse', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q2->id, 'option_text' => 'Printer', 'is_correct' => false]);

        // Soal 3: Benar Salah
        $q3 = Question::create([
            'exam_section_id' => $sectionMain->id,
            'user_id' => 1,
            'type' => 'true_false',
            'content' => '<p>HTML adalah bahasa pemrograman.</p>',
            'explanation' => '<p>Pernyataan ini <strong>salah</strong>. HTML adalah bahasa markup yang digunakan untuk menyusun struktur halaman web, bukan bahasa pemrograman.</p>',
        ]);
        QuestionOption::create(['question_id' => $q3->id, 'option_text' => 'Benar', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q3->id, 'option_text' => 'Salah', 'is_correct' => false]);

        // Soal 4: Menjodohkan
        $q4 = Question::create([
            'exam_section_id' => $sectionMain->id,
            'user_id' => 1,
            'type' => 'matching',
            'content' => '<p>Pasangkan istilah jaringan berikut dengan fungsinya!</p>',
            'explanation' => '<p>LAN adalah Local Area Network, WAN adalah Wide Area Network, HTTP adalah protokol transfer untuk komunikasi web, dan IP adalah Internet Protocol untuk pengalamatan perangkat dalam jaringan.</p>',
        ]);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'LAN', 'target_text' => 'Local Area Network']);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'WAN', 'target_text' => 'Wide Area Network']);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'HTTP', 'target_text' => 'Protocol Transfer']);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'IP', 'target_text' => 'Internet Protocol']);

        // Soal 5: Essay
        $q5 = Question::create([
            'exam_section_id' => $sectionMain->id,
            'user_id' => 1,
            'type' => 'essay',
            'content' => '<p>Sebutkan ibukota Jawa Barat?</p>',
            'explanation' => '<p>Ibu kota Provinsi Jawa Barat adalah <strong>Bandung</strong>.</p>',
        ]);
        QuestionOption::create(['question_id' => $q5->id, 'option_text' => 'Bandung', 'is_correct' => true]);

        // ---------------------------------------------------
        // 8. INPUT SOAL TKP (DIMASUKKAN KE SECTION TKP)
        // ---------------------------------------------------
        $q6 = Question::create([
            'exam_section_id' => $sectionTkp->id,
            'user_id' => 1,
            'type' => 'tkp',
            'content' => '<p>Saat rekan kerja mengalami kesulitan tugas, saya akan...</p>',
            'explanation' => '<p>Dalam situasi kerja sama, pilihan yang paling mencerminkan kepedulian dan kolaborasi adalah membantu rekan menyelesaikan tugas. Setiap pilihan TKP memiliki bobot sesuai tingkat kesesuaian perilakunya.</p>',
        ]);
        // Tidak menggunakan is_correct, melainkan score_weight
        QuestionOption::create(['question_id' => $q6->id, 'option_text' => 'Membantu menyelesaikannya', 'score_weight' => 5]);
        QuestionOption::create(['question_id' => $q6->id, 'option_text' => 'Memberi petunjuk secukupnya', 'score_weight' => 4]);
        QuestionOption::create(['question_id' => $q6->id, 'option_text' => 'Melihat situasi terlebih dahulu', 'score_weight' => 3]);
        QuestionOption::create(['question_id' => $q6->id, 'option_text' => 'Bukan urusan saya', 'score_weight' => 2]);
        QuestionOption::create(['question_id' => $q6->id, 'option_text' => 'Menyalahkan karena tidak bisa', 'score_weight' => 1]);

        $this->command->info('All questions seeded successfully!');
    }
}
