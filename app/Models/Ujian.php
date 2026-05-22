<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Ujian
 * Merepresentasikan data ujian yang dikelola oleh Admin.
 */
class Ujian extends Model
{
    protected $table = 'ujians';
    protected $primaryKey = 'ujian_id';

    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'durasi',
        'status',
        'passing_grade',
        'started_at',
        'sisa_waktu',
    ];

    protected $casts = [
        'passing_grade' => 'integer',
        'started_at' => 'datetime',
    ];

    /**
     * Relasi ke model Soal.
     * Satu ujian memiliki banyak soal.
     */
    public function soals()
    {
        return $this->hasMany(Soal::class, 'ujian_id', 'ujian_id');
    }

    /**
     * Relasi ke model User (Admin pembuat ujian).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Mendapatkan sisa waktu ujian dalam detik secara real-time.
     * Setiap kali ujian dimulai, countdown selalu dari durasi penuh (durasi * 60).
     * Saat tidak aktif, sisa waktu = 0.
     */
    public function getRemainingSeconds()
    {
        // Hanya hitung sisa waktu jika ujian sedang aktif dan sudah dimulai
        if ($this->status !== 'active' || !$this->started_at) {
            return 0;
        }

        $startedAt = $this->started_at instanceof \Carbon\Carbon ? $this->started_at : \Carbon\Carbon::parse($this->started_at);
        // Gunakan selisih Unix timestamp langsung agar tidak terpengaruh
        // oleh perilaku signed/unsigned diffInSeconds di Carbon 3.x
        $elapsed = time() - $startedAt->timestamp;
        $remaining = ($this->durasi * 60) - $elapsed;

        return (int) max(0, $remaining);
    }

    /**
     * Memeriksa apakah waktu ujian telah habis.
     * Jika habis, otomatis mengubah status menjadi closed (di-pause) dan menonaktifkan token.
     */
    public function checkTimeout()
    {
        if ($this->status === 'active' && $this->started_at) {
            $remaining = $this->getRemainingSeconds();
            if ($remaining <= 0) {
                $this->status = 'closed';
                $this->sisa_waktu = 0;
                $this->started_at = null;
                $this->save();

                // Nonaktifkan semua token ujian ini
                \App\Models\Token::where('ujian_id', $this->ujian_id)->update(['status_aktif' => false]);
            }
        }
    }
}
