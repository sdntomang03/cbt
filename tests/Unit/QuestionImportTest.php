<?php

namespace Tests\Unit;

use App\Imports\QuestionImport;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    public function test_import_accepts_array_rows_from_excel_preview(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jenis soal tidak dikenal: not_a_type');

        $import = new QuestionImport(1, 1, 1, null);

        $import->collection(new Collection([
            ['narasi_soal' => 'Contoh soal', 'jenis_soal' => 'not_a_type'],
        ]));
    }

    public function test_import_preserves_latex_source_and_surrounding_whitespace(): void
    {
        $import = new QuestionImport(1, 1, 1, null);
        $method = new \ReflectionMethod($import, 'value');
        $latex = '  $\\frac{a}{b} + \\sqrt{x}$  ';

        $this->assertSame($latex, $method->invoke($import, collect([
            'narasi_soal' => $latex,
        ]), 'narasi_soal'));
    }
}
