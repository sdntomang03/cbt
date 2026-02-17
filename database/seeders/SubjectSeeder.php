<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Matematika',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'PKn',
            'IPAS',
        ];

        foreach ($subjects as $name) {
            // firstOrCreate mencegah duplikasi jika seeder dijalankan 2x
            Subject::firstOrCreate(['name' => $name]);
        }
    }
}
