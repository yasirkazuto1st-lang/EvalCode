<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'soal_id', 'soal_id');
    }
}
