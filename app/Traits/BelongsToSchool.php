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

                if (! $user->hasRole('admin')) {
                    // Gunakan nama tabel secara spesifik agar tidak error jika ada fitur JOIN
                    $builder->where($builder->getModel()->getTable().'.school_id', $user->school_id);
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

                if (! $user->hasRole('admin')) {
                    $model->school_id = $user->school_id;
                }
            }
        });
    }
}
