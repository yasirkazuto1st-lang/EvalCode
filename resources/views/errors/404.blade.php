@extends('errors.layout')

@section('title', '404 - Halaman Tidak Ditemukan')

@section('error-content')
    <div class="error-icon">
        <i class="bi bi-compass-fill"></i>
    </div>
    <div class="error-code">404</div>
    <h1 class="error-title">Halaman Tidak Ditemukan</h1>
    <p class="error-message">
        Maaf, halaman atau URL yang Anda tuju tidak tersedia atau telah dipindahkan. Silakan periksa kembali penulisan URL Anda.
    </p>
@endsection
