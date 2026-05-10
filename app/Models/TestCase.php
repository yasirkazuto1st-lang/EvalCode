<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestCase extends Model
{
    protected $table = 'test_cases';
    protected $primaryKey = 'test_case_id';

    protected $fillable = [
        'soal_id',
        'input',
        'expected_output',
    ];

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id', 'soal_id');
    }
}
