<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $ids;

    // Terima ID yang dilempar dari Controller
    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function query()
    {
        $query = User::query()->with('school');

        // Jika ada ID yang dipilih, filter datanya. Jika kosong, biarkan semua data.
        if (! empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Username/NISN',
            'Email',
            'Sekolah',
            'Role',
        ];
    }

    public function map($user): array
    {
        // Ambil nama role (karena menggunakan Spatie/Permission)
        $roleName = $user->roles->pluck('name')->first() ?? '-';

        return [
            $user->id,
            $user->name,
            $user->username,
            $user->email ?? '-',
            $user->school ? $user->school->name : '-',
            strtoupper($roleName),
        ];
    }
}
