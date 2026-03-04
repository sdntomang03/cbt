<?php

namespace App\Exports;

use App\Models\School;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SchoolsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $ids;

    // Terima array ID dari Controller
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    // Query khusus untuk ID yang dicentang
    public function query()
    {
        return School::query()->whereIn('id', $this->ids);
    }

    // Mengatur nama kolom di file Excel
    public function headings(): array
    {
        return [
            'ID',
            'Nama Sekolah',
            'Domain',
            'Tanggal Dibuat',
        ];
    }

    // Memetakan data apa saja yang masuk ke baris Excel
    public function map($school): array
    {
        return [
            $school->id,
            $school->name,
            $school->domain ?? '-',
            $school->created_at->format('d-m-Y H:i'),
        ];
    }
}
