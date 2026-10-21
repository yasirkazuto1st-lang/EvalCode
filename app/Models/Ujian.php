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
        'token_interval',
        'status',
        'passing_grade',
        'max_attempt',
        'bahasa_pemrograman',
        'started_at',
        'sisa_waktu',
    ];

    protected $casts = [
        'durasi' => 'integer',
        'token_interval' => 'integer',
        'passing_grade' => 'integer',
        'max_attempt' => 'integer',
        'bahasa_pemrograman' => 'array',
        'started_at' => 'datetime',
    ];

    /**
     * Mendapatkan daftar bahasa pemrograman yang diizinkan untuk ujian ini.
     * Default ke semua bahasa (python, cpp, java, dart) jika belum diatur.
     */
    public function getAllowedLanguages(): array
    {
        if (is_array($this->bahasa_pemrograman) && !empty($this->bahasa_pemrograman)) {
            return $this->bahasa_pemrograman;
        }
        if (is_string($this->bahasa_pemrograman)) {
            $decoded = json_decode($this->bahasa_pemrograman, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return ['python', 'cpp', 'java', 'dart'];
    }

    /**
     * Format durasi detik pengerjaan menjadi format terbaca (misal: "25m 40s" atau "01j 12m 30s").
     */
    public static function formatWaktuPengerjaan(?int $seconds): string
    {
        if ($seconds === null || $seconds < 0) {
            return '-';
        }
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        if ($h > 0) {
            return sprintf('%02dj %02dm %02ds', $h, $m, $s);
        }
        return sprintf('%02dm %02ds', $m, $s);
    }

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

    protected static function booted()
    {
        static::saved(function ($ujian) {
            \Illuminate\Support\Facades\Cache::forget('active_exams');
            \Illuminate\Support\Facades\Cache::forget('closed_exams');
            \Illuminate\Support\Facades\Cache::forget('finished_exams');
            \Illuminate\Support\Facades\Cache::forget('active_exams_with_count');
            \Illuminate\Support\Facades\Cache::forget('closed_exams_with_count');
            \Illuminate\Support\Facades\Cache::forget('finished_exams_with_count');
        });

        static::deleted(function ($ujian) {
            \Illuminate\Support\Facades\Cache::forget('active_exams');
            \Illuminate\Support\Facades\Cache::forget('closed_exams');
            \Illuminate\Support\Facades\Cache::forget('finished_exams');
            \Illuminate\Support\Facades\Cache::forget('active_exams_with_count');
            \Illuminate\Support\Facades\Cache::forget('closed_exams_with_count');
            \Illuminate\Support\Facades\Cache::forget('finished_exams_with_count');
        });
    }
}
