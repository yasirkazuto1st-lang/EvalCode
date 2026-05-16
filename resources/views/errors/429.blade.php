@extends('errors.layout')

@section('title', '429 - Terlalu Banyak Permintaan')

@section('error-content')
    <div class="error-icon">
        <i class="bi bi-speedometer2"></i>
    </div>
    <div class="error-code">429</div>
    <h1 class="error-title">Terlalu Banyak Permintaan</h1>
    <p class="error-message">
        Maaf, sistem mendeteksi terlalu banyak permintaan dari perangkat Anda dalam waktu singkat. Silakan tunggu beberapa saat sebelum mencoba lagi.
    </p>
@endsection
