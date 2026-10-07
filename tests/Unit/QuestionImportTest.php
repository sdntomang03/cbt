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

    public function test_import_stores_quill_formula_as_latex_source_without_rendered_katex_markup(): void
    {
        $import = new QuestionImport(1, 1, 1, null);
        $method = new \ReflectionMethod($import, 'value');
        $formula = '120\\% - 3 + 2 \\times 0.75 + \\frac{2}{3} = \\ldots';
        $html = '<p>Hitung <span class="ql-formula" data-value="'.$formula.'">'
            .'<span contenteditable="false"><span class="katex"><math><annotation encoding="application/x-tex">'
            .$formula.'</annotation></math></span></span></span>.</p>';

        $stored = $method->invoke($import, collect(['narasi_soal' => $html]), 'narasi_soal');

        $this->assertStringContainsString('class="ql-formula"', $stored);
        $this->assertStringContainsString('data-value="'.$formula.'"', $stored);
        $this->assertStringNotContainsString('katex', $stored);
        $this->assertStringNotContainsString('application/x-tex', $stored);
        $this->assertStringContainsString('<p>Hitung ', $stored);
    }
}
