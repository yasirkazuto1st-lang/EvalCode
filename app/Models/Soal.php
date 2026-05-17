<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Soal
 * Merepresentasikan data soal (berupa PDF) dan bobot nilainya untuk suatu Ujian.
 */
class Soal extends Model
{
    protected $table = 'soals';
    protected $primaryKey = 'soal_id';

    protected $fillable = [
        'ujian_id',
        'nama_soal',
        'soal_pdf',
        'bobot_nilai',
    ];

    protected $casts = [
        'bobot_nilai' => 'integer',
    ];

    /**
     * Relasi ke model Ujian.
     * Satu soal dimiliki oleh satu ujian.
     */
    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    /**
     * Relasi ke model TestCase.
     * Satu soal memiliki banyak test case (input/output).
     */
    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'soal_id', 'soal_id');
    }
}
