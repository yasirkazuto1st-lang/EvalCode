@extends('errors.layout')

@section('title', '419 - Sesi Kadaluarsa')

@section('error-content')
    <div class="error-icon">
        <i class="bi bi-hourglass-split"></i>
    </div>
    <div class="error-code">419</div>
    <h1 class="error-title">Sesi Kadaluarsa</h1>
    <p class="error-message">
        Maaf, sesi Anda telah berakhir karena terlalu lama tidak beraktivitas atau token keamanan telah usang. Silakan muat ulang halaman atau login kembali.
    </p>
@endsection
