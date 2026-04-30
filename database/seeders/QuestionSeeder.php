<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamSession; // Import model baru
use App\Models\ExamType;
use App\Models\Question;
use App\Models\QuestionMatch;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        // ---------------------------------------------------
        // 1. BUAT JENIS UJIAN (EXAM TYPE) - BARU
        // ---------------------------------------------------
        $examType = ExamType::create([
            'school_id' => 1,
            'name' => 'Penilaian Harian',
        ]);

        $this->command->info('Exam Type created: '.$examType->name);

        // ---------------------------------------------------
        // 2. BUAT UJIAN (EXAM) - DISESUAIKAN
        // ---------------------------------------------------
        $title = 'Ujian Simulasi Fitur Lengkap';
        $exam = Exam::create([
            'school_id' => 1,
            'teacher_id' => 1,
            'exam_type_id' => $examType->id, // HUBUNGKAN KE EXAM TYPE
            'title' => $title,
            'slug' => Str::slug($title.'-'.Str::random(5)),
            'duration_minutes' => 60,
            'random_question' => true,
            'random_answer' => true,
            'status' => 'published',
        ]);

        $this->command->info('Exam created: '.$exam->title);

        // ---------------------------------------------------
        // 3. BUAT SESI UJIAN (EXAM SESSION)
        // ---------------------------------------------------
        $session = ExamSession::create([
            'exam_id' => $exam->id,
            'session_name' => 'Sesi Uji Coba - Kelas A',
            'token' => 'ABC123',
            'school_id' => 1,
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHours(3),
        ]);

        $this->command->info('Session created: Token '.$session->token);

        // ---------------------------------------------------
        // 4. DAFTARKAN SISWA KE SESI (PIVOT)
        // ---------------------------------------------------
        $session->students()->attach(3, [
            'status' => 'not_started',
            'score' => 0,
            'is_locked' => false,
            'violation_count' => 0,
            'school_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Student attached to session.');

        // ---------------------------------------------------
        // 5. INPUT SOAL-SOAL
        // ---------------------------------------------------

        // Soal 1: Pilihan Ganda
        $q1 = Question::create([
            'exam_id' => $exam->id,
            'user_id' => 1,
            'type' => 'single_choice',
            'content' => '<p>4+4 = ...</p>',
            'school_id' => 1,
        ]);
        QuestionOption::create(['question_id' => $q1->id, 'school_id' => 1, 'option_text' => '6', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q1->id, 'school_id' => 1, 'option_text' => '8', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q1->id, 'school_id' => 1, 'option_text' => '9', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q1->id, 'school_id' => 1, 'option_text' => '12', 'is_correct' => false]);

        // Soal 2: Pilihan Ganda Kompleks
        $q2 = Question::create([
            'exam_id' => $exam->id,
            'user_id' => 1,
            'type' => 'complex_choice',
            'content' => '<p>Manakah yang termasuk perangkat <strong>Input</strong> komputer? (Pilih lebih dari satu)</p>',
            'school_id' => 1,
        ]);
        QuestionOption::create(['question_id' => $q2->id, 'school_id' => 1, 'option_text' => 'Monitor', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q2->id, 'school_id' => 1, 'option_text' => 'Keyboard', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q2->id, 'school_id' => 1, 'option_text' => 'Mouse', 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q2->id, 'school_id' => 1, 'option_text' => 'Printer', 'is_correct' => false]);

        // Soal 3: Benar Salah
        $q3 = Question::create([
            'exam_id' => $exam->id,
            'user_id' => 1,
            'type' => 'true_false',
            'content' => '<p>HTML adalah bahasa pemrograman.</p>',
            'school_id' => 1,
        ]);
        QuestionOption::create(['question_id' => $q3->id, 'school_id' => 1, 'option_text' => 'Benar', 'is_correct' => false]);
        QuestionOption::create(['question_id' => $q3->id, 'school_id' => 1, 'option_text' => 'Salah', 'is_correct' => true]);

        // Soal 4: Menjodohkan
        $q4 = Question::create([
            'exam_id' => $exam->id,
            'user_id' => 1,
            'type' => 'matching',
            'content' => '<p>Pasangkan istilah jaringan berikut dengan fungsinya!</p>',
            'school_id' => 1,
        ]);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'LAN', 'target_text' => 'Local Area Network', 'school_id' => 1]);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'WAN', 'target_text' => 'Wide Area Network', 'school_id' => 1]);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'HTTP', 'target_text' => 'Protocol Transfer', 'school_id' => 1]);
        QuestionMatch::create(['question_id' => $q4->id, 'premise_text' => 'IP', 'target_text' => 'Internet Protocol', 'school_id' => 1]);

        // Soal 5: Essay
        $q5 = Question::create([
            'exam_id' => $exam->id,
            'user_id' => 1,
            'type' => 'essay',
            'content' => '<p>Sebutkan ibukota Jawa Barat?</p>',
            'school_id' => 1,
        ]);
        QuestionOption::create(['question_id' => $q5->id, 'school_id' => 1, 'option_text' => 'Bandung', 'is_correct' => true]);
    }
}
