<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model UjianSession
 * Mencatat riwayat setiap sesi ujian resmi yang dimulai oleh Pengawas/Admin.
 * Digunakan untuk melacak waktu mulai (started_at) dan berakhir (ended_at)
 * tiap sesi secara pasti dan terisolasi dari perputaran token.
 */
class UjianSession extends Model
{
    protected $table = 'ujian_sessions';
    protected $primaryKey = 'session_id';

    protected $fillable = [
        'ujian_id',
        'sesi_ke',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'sesi_ke' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Relasi ke model Ujian.
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }
}
