<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool()
    {
        // 1. FILTER OTOMATIS SAAT MENGAMBIL DATA (SELECT)
        static::addGlobalScope('school', function (Builder $builder) {

            // Jangan jalankan saat proses perintah Terminal/Artisan (seperti migrate)
            if (app()->runningInConsole()) {
                return;
            }

            // KUNCI UTAMA: Gunakan Auth::hasUser() untuk mencegah Infinite Loop di Model User
            if (Auth::hasUser()) {
                $user = Auth::user();

                // Jika user BUKAN admin (misal: guru atau siswa)
                if (! $user->hasRole('admin')) {
                    $tableName = $builder->getModel()->getTable();

                    // BUNGKUS DENGAN CLOSURE (Fungsi) agar query menjadi (A OR B)
                    // Jika tidak dibungkus, orWhereNull akan merusak query (Where C And A Or B)
                    $builder->where(function ($query) use ($tableName, $user) {
                        $query->where($tableName.'.school_id', $user->school_id)
                            ->orWhereNull($tableName.'.school_id'); // <-- Membaca Data Global Superadmin
                    });
                }
            }
        });

        // 2. ISI OTOMATIS SAAT MENAMBAH DATA BARU (INSERT)
        static::creating(function ($model) {

            if (app()->runningInConsole()) {
                return;
            }

            if (Auth::hasUser()) {
                $user = Auth::user();

                // Jika BUKAN admin, paksa school_id sesuai sekolah user yang membuat
                // Jika Admin, baris ini dilewati, sehingga school_id default-nya adalah NULL (Global)
                if (! $user->hasRole('admin')) {
                    $model->school_id = $user->school_id;
                }
            }
        });
    }
}
