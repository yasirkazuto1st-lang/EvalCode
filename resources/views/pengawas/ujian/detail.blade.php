@extends('layouts.sidebar')

@section('title', 'Detail Monitoring Ujian')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<style>
    .rotate-180 { transform: rotate(180deg); }
</style>
<div class="container-fluid py-4">
    <!-- Back Button -->
    <a href="{{ route('pengawas.dashboard') }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Header Informasi Ujian -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 detail-ujian-header position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
        <!-- Decorative Background Icon -->
        <i class="bi bi-laptop position-absolute opacity-10" style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>
        
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
            <div class="mb-3 mb-md-0">
                <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold"><i class="bi bi-circle-fill text-success small me-1"></i> Sedang Berlangsung</span>
                <h4 class="fw-bold mb-1">Ujian Komprehensif Dasar Pemrograman</h4>
                <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                    <span><i class="bi bi-clock me-1"></i> Waktu Ujian: 120 Menit</span>
                    <span><i class="bi bi-check2-square me-1"></i> Passing Grade: 75</span>
                </div>
            </div>
            <div class="d-flex flex-column gap-2" style="min-width: 200px;">
                <div class="bg-black bg-opacity-25 rounded-4 p-2 px-3 border border-white border-opacity-25 shadow-sm text-center" style="backdrop-filter: blur(10px);">
                    <span class="small opacity-75 d-block text-uppercase fw-semibold" style="letter-spacing: 1px; font-size: 10px;">Token Ujian Aktif</span>
                    <h4 class="fw-bold font-monospace mb-0 text-white" style="letter-spacing: 3px;">A7X-92Q</h4>
                    <small class="d-block opacity-50 mt-1" style="font-size: 10px;"><i class="bi bi-arrow-repeat me-1"></i>Diperbarui tiap 1 menit</small>
                </div>
                <button id="toggleUjianBtn" class="btn btn-danger w-100 fw-bold shadow-sm rounded-pill" onclick="toggleUjian()">
                    <i class="bi bi-stop-circle-fill me-2"></i> Akhiri Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- 2 Kolom Konten -->
    <div class="row g-4">
        <!-- Kiri: Daftar Soal -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('pengawas.ujian.soal') }}" class="list-group-item list-group-item-action border-0 py-3 rounded mb-2 bg-light shadow-sm d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-bold text-dark">1. Hello World & Basic I/O</span>
                                <span class="badge bg-success rounded-pill">115/120</span>
                            </div>
                            <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                <div class="progress-bar bg-success rounded-pill" role="progressbar" style="width: 95%;" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-success fw-semibold mt-1" style="font-size: 0.75rem;"><i class="bi bi-check-circle-fill me-1"></i>Mudah (95% Selesai)</small>
                        </a>
                        
                        <a href="{{ route('pengawas.ujian.soal') }}" class="list-group-item list-group-item-action border py-3 rounded mb-2 d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-semibold text-dark">2. Deret Fibonacci</span>
                                <span class="badge bg-primary rounded-pill">85/120</span>
                            </div>
                            <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-primary fw-semibold mt-1" style="font-size: 0.75rem;"><i class="bi bi-info-circle-fill me-1"></i>Normal (70% Selesai)</small>
                        </a>
                        
                        <a href="{{ route('pengawas.ujian.soal') }}" class="list-group-item list-group-item-action border py-3 rounded mb-2 d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-semibold text-dark">3. Knapsack Problem (DP)</span>
                                <span class="badge bg-danger rounded-pill">15/120</span>
                            </div>
                            <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                <div class="progress-bar bg-danger rounded-pill" role="progressbar" style="width: 12%;" aria-valuenow="12" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-danger fw-semibold mt-1" style="font-size: 0.75rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i>Sangat Sulit (12% Selesai)</small>
                        </a>
                        
                        <a href="{{ route('pengawas.ujian.soal') }}" class="list-group-item list-group-item-action border py-3 rounded mb-2 d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="fw-semibold text-dark">4. Binary Search Tree</span>
                                <span class="badge bg-warning text-dark rounded-pill">40/120</span>
                            </div>
                            <div class="progress bg-secondary bg-opacity-25" style="height: 6px;">
                                <div class="progress-bar bg-warning rounded-pill" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-warning fw-bold mt-1" style="font-size: 0.75rem;"><i class="bi bi-activity me-1"></i>Sulit (33% Selesai)</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanan: Table Monitoring Peserta -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-primary me-2"></i> Monitoring Peserta Ujian</h5>
                    <div class="input-group w-auto shadow-sm rounded-pill overflow-hidden">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 shadow-none px-0" placeholder="Cari Mahasiswa...">
                        <button class="btn btn-primary px-3 fw-semibold">Cari</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-nowrap border-bottom border-primary border-2">
                                    <th class="ps-4 py-3">NIM</th>
                                    <th class="py-3">Nama Mahasiswa</th>
                                    <th class="py-3">Status Pengerjaan</th>
                                    <th class="py-3">Similarity Tertinggi</th>
                                    <th class="py-3">Total Skor</th>
                                    <th class="pe-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($participants as $p)
                                    <tr>
                                        <td class="ps-4">{{ $p->nim }}</td>
                                        <td class="fw-semibold">{{ $p->name }}</td>
            <td>{!! $p->status_badge !!}</td>
            <td>
                @php
                    $pColor = $p->similarity >= 70 ? 'danger' : ($p->similarity >= 40 ? 'warning text-dark' : 'success');
                @endphp
                <span class="badge bg-{{ $pColor }}">{{ $p->similarity }}%</span>
            </td>
            <td>{{ $p->total_score ?? 0 }}</td>
                                        <td class="pe-4 text-center">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubmissions{{ $p->id }}" aria-expanded="false" aria-controls="collapseSubmissions{{ $p->id }}" onclick="this.querySelector('.bi-chevron-down').classList.toggle('rotate-180')">
                                                Detail <i class="bi bi-chevron-down ms-1" style="transition: transform 0.3s ease; display: inline-block;"></i>
                                            </button>
                                        </td>
        </tr>
        <tr>
                                        <td colspan="6" class="p-0 border-0">
                                            <div class="collapse" id="collapseSubmissions{{ $p->id }}">
                                                <div class="card card-body border-0 bg-light rounded-0 border-start border-4 border-primary m-3 shadow-sm">
                        <h6 class="fw-bold mb-3">Riwayat Submission</h6>
                        <!-- Submission table can be rendered here -->
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Soal</th>
                                    <th>Status</th>
                                    <th>Skor Asli</th>
                                    <th>Similarity</th>
                                    <th>Edit Skor/Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($p->submissions as $s)
                                    <tr>
                                        <td>{{ $s->time }}</td>
                                        <td>{{ $s->question }}</td>
                                        <td>{!! $s->status_badge !!}</td>
                                        <td>{{ $s->score }}</td>
                                        <td>
                                            @php
                                                $sColor = $s->similarity >= 70 ? 'danger' : ($s->similarity >= 40 ? 'warning text-dark' : 'success');
                                            @endphp
                                            <span class="badge bg-{{ $sColor }}">{{ $s->similarity }}%</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning py-0"><i class="bi bi-pencil-square"></i> Override</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </td>
        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada peserta yang memulai ujian ini.</td></tr>
                                    @endforelse
                                </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3 rounded-bottom-4">
                    <div class="d-flex justify-content-between align-items-center px-2">
                        <span class="text-muted small fw-semibold">Menampilkan 1 hingga 10 dari 120 peserta</span>
                        <div class="m-0">
                            {{ $participants->links() }}
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let ujianActive = true;

    function toggleUjian() {
        const btn = document.getElementById('toggleUjianBtn');
        ujianActive = !ujianActive;

        if (ujianActive) {
            btn.className = 'btn btn-danger w-100 fw-bold shadow-sm rounded-pill';
            btn.innerHTML = '<i class="bi bi-stop-circle-fill me-2"></i> Akhiri Ujian';
        } else {
            btn.className = 'btn btn-light w-100 fw-bold shadow-sm rounded-pill text-primary';
            btn.innerHTML = '<i class="bi bi-play-circle-fill me-2"></i> Mulai Ujian';
        }
    }
</script>
@endsection
