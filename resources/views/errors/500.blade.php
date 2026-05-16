@extends('errors.layout')

@section('title', '500 - Terjadi Kesalahan Sistem')

@section('error-content')
    <div class="error-icon">
        <i class="bi bi-hdd-network-fill"></i>
    </div>
    <div class="error-code">500</div>
    <h1 class="error-title">Terjadi Kesalahan Sistem</h1>
    <p class="error-message">
        Maaf, server kami sedang mengalami kendala teknis atau koneksi jaringan terputus. Tim teknis kami sedang berusaha memulihkannya.
    </p>
@endsection
