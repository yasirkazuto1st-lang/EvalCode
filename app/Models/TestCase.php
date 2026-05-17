<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model TestCase
 * Merepresentasikan skenario input dan output yang diharapkan untuk suatu Soal saat dievaluasi oleh Compiler.
 */
class TestCase extends Model
{
    protected $table = 'test_cases';
    protected $primaryKey = 'test_case_id';

    protected $fillable = [
        'soal_id',
        'input',
        'expected_output',
    ];

    /**
     * Relasi ke model Soal.
     * Satu test case dimiliki oleh satu soal.
     */
    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id', 'soal_id');
    }
}
