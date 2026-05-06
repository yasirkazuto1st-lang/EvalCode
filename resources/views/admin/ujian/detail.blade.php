@extends('layouts.sidebar')

@section('title', 'Detail Ujian')

@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.dashboard') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-speedometer2 sidebar-icon"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('admin.ujian.index') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Manajemen Soal</span>
    </a>
    <a href="{{ route('admin.users') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.users') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-people sidebar-icon"></i> <span class="sidebar-text">Manajemen User</span>
    </a>
@endsection

@section('content')
<style>
    .rotate-180 { transform: rotate(180deg); }
</style>
<div class="container-fluid py-4">
    <!-- Back Button -->
    <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Header Exam Info -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
        <i class="bi bi-journal-code position-absolute opacity-10" style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>
        
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
            <div class="mb-3 mb-md-0">
                <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold"><i class="bi bi-pencil-square small me-1"></i> Mode Manajemen Ujian</span>
                <h4 class="fw-bold mb-1">{{ $exam->title }}</h4>
                <p class="mb-2 opacity-75 fs-6">{{ $exam->description }}</p>
                <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                    <span><i class="bi bi-clock me-1"></i> Durasi: {{ $exam->duration }} Menit</span>
                    <span><i class="bi bi-check2-square me-1"></i> Passing Grade: {{ $exam->passing_grade }}%</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Table Soal -->
        <div class="col-lg-4 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                    <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Soal
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-nowrap border-bottom border-primary border-2">
                                    <th class="ps-4 py-3">#</th>
                                    <th class="py-3">Nama Soal</th>
                                    <th class="py-3">Bobot</th>
                                    <th class="pe-4 py-3 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $q)
                                    <tr>
                                        <td>{{ $q['id'] }}</td>
                                        <td>{{ $q['name'] }}</td>
                                        <td>{{ $q['weight'] }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.ujian.soal.detail', ['examId' => $exam->id, 'soalId' => $q['id']]) }}" class="btn btn-sm btn-outline-primary me-1">
                                                Detail
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editQuestionModal{{ $q['id'] }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestionModal{{ $q['id'] }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-start">Tidak ada soal.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Table Peserta -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-warning me-2"></i> Peserta Ujian</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden" style="width: 250px;">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 shadow-none px-0" placeholder="Cari Mahasiswa...">
                        </div>
                        <button class="btn btn-outline-secondary rounded-pill shadow-sm text-nowrap px-3">
                            <i class="bi bi-printer me-1"></i> Cetak Laporan
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-nowrap">
                                    <th class="ps-4 py-3">NIM</th>
                                    <th class="py-3">Nama</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Similarity</th>
                                    <th class="py-3">Skor</th>
                                    <th class="py-3">Lulus</th>
                                    <th class="pe-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($participants as $p)
                                    <tr>
                                        <td class="ps-4">{{ $p->nim }}</td>
                                        <td class="fw-semibold">{{ $p->name }}</td>
                                        <td>{{ $p->status_badge }}</td>
                                        <td>
                                            @php
                                                $pColor = $p->similarity >= 70 ? 'danger' : ($p->similarity >= 40 ? 'warning text-dark' : 'success');
                                            @endphp
                                            <span class="badge bg-{{ $pColor }}">{{ $p->similarity }}%</span>
                                        </td>
                                        <td>{{ $p->total_score ?? 0 }}</td>
                                        <td>{!! $p->status_lulus !!}</td>
                                        <td class="pe-4 text-center">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubmissions{{ $p->id }}" aria-expanded="false" aria-controls="collapseSubmissions{{ $p->id }}" onclick="this.querySelector('.bi-chevron-down').classList.toggle('rotate-180')">
                                                Detail <i class="bi bi-chevron-down ms-1" style="transition: transform 0.3s ease; display: inline-block;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Collapse Row for Submission History -->
                                    <tr class="p-0 border-0">
                                        <td colspan="7" class="p-0 border-0">
                                            <div class="collapse" id="collapseSubmissions{{ $p->id }}">
                                                <div class="card card-body border-0 bg-light rounded-0 border-start border-4 border-primary m-3 shadow-sm">
                                                <h6 class="fw-bold mb-3 small text-muted text-uppercase">Riwayat Submission</h6>
                                                <table class="table table-sm table-bordered bg-white mb-0">
                                                    <thead class="table-secondary small text-nowrap">
                                                        <tr>
                                                            <th>Waktu</th>
                                                            <th>Soal</th>
                                                            <th>Status</th>
                                                            <th>Skor</th>
                                                            <th>Similarity</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="small">
                                                        @foreach($p->submissions as $sub)
                                                        <tr>
                                                            <td>{{ $sub->time }}</td>
                                                            <td>{{ $sub->question }}</td>
                                                            <td>{!! $sub->status_badge !!}</td>
                                                            <td>{{ $sub->score }}</td>
                                                            <td>
                                                                @php
                                                                    $subColor = $sub->similarity >= 70 ? 'danger' : ($sub->similarity >= 40 ? 'warning text-dark' : 'success');
                                                                @endphp
                                                                <span class="badge bg-{{ $subColor }}">{{ $sub->similarity }}%</span>
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
                                    <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada peserta.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3 rounded-bottom-4">
                    <div class="d-flex justify-content-between align-items-center px-2">
                        <span class="text-muted small fw-semibold">Menampilkan total 120 peserta</span>
                        <!-- pagination placeholder -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" aria-labelledby="addQuestionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addQuestionModalLabel">Tambah Soal Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label class="form-label">Nama Soal</label>
            <input type="text" class="form-control" placeholder="Contoh: Soal 1 - Hello World">
          </div>
          <div class="mb-3">
            <label class="form-label">File PDF Soal</label>
            <input type="file" class="form-control" accept="application/pdf">
          </div>
          <div class="mb-3">
            <label class="form-label">Bobot Nilai</label>
            <input type="number" class="form-control" placeholder="Bobot (misal: 20)">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>
@endsection
