<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function soals()
    {
        return $this->hasMany(Soal::class, 'ujian_id', 'ujian_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
