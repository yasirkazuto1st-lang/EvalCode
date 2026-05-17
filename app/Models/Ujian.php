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
    ];

    protected $casts = [
        'passing_grade' => 'integer',
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
}
