@extends('errors.layout')

@section('title', '403 - Akses Ditolak')

@section('error-content')
    <div class="error-icon">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <div class="error-code">403</div>
    <h1 class="error-title">Akses Ditolak</h1>
    <p class="error-message">
        Maaf, Anda tidak memiliki izin atau hak akses untuk melihat halaman ini. Silakan kembali ke dashboard role Anda.
    </p>
@endsection
