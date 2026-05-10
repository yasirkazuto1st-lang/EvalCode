<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Token extends Model
{
    protected $table = 'tokens';
    protected $primaryKey = 'token_id';

    protected $fillable = [
        'ujian_id',
        'kode_token',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function ujian()
    {
        return $this->belongsTo(Ujian::class, 'ujian_id', 'ujian_id');
    }

    /**
     * Generate a random 6-character alphanumeric token (e.g. A7X92Q)
     */
    public static function generateCode(): string
    {
        return strtoupper(Str::random(3)) . '-' . strtoupper(Str::random(3));
    }
}
