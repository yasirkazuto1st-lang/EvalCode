@extends('layouts.sidebar')

@section('title', 'Detail Ujian')

@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.dashboard') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-speedometer2 sidebar-icon"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('admin.ujian.index') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Manajemen Ujian</span>
    </a>
    <a href="{{ route('admin.users') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.users') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-people sidebar-icon"></i> <span class="sidebar-text">Manajemen User</span>
    </a>
@endsection

@section('content')
    <style>
        .rotate-180 {
            transform: rotate(180deg);
        }
    </style>
    <div class="container-fluid py-4">
        <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-back mb-3"><i class="bi bi-arrow-left"></i></a>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Header Exam Info -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 position-relative overflow-hidden"
            style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
            <i class="bi bi-journal-code position-absolute opacity-10"
                style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>
            <div
                class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
                <div class="mb-3 mb-md-0">
                    <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-pencil-square small me-1"></i> Mode Manajemen Ujian
                    </span>
                    <h4 class="fw-bold mb-1">{{ $exam->judul }}</h4>
                    <p class="mb-2 opacity-75 fs-6">{{ $exam->deskripsi }}</p>
                    <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                        <span><i class="bi bi-clock me-1"></i> Durasi: {{ $exam->durasi }} Menit</span>
                        <span><i class="bi bi-check2-square me-1"></i> Passing Grade: {{ $exam->passing_grade }} Pts</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Table Soal -->
            <div class="col-lg-5 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#addQuestionModal">
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
                                    @forelse($questions as $index => $q)
                                        <tr>
                                            <td class="ps-4">{{ $index + 1 }}</td>
                                            <td>{{ $q->nama_soal }}</td>
                                            <td>{{ $q->bobot_nilai }}</td>
                                            <td class="text-nowrap text-end pe-4">
                                                <a href="{{ route('admin.ujian.soal.detail', ['examId' => $exam->ujian_id, 'soalId' => $q->soal_id]) }}"
                                                    class="btn btn-sm btn-outline-primary me-1">Detail</a>
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editSoalModal{{ $q->soal_id }}"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteSoalModal{{ $q->soal_id }}"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>

                                        <!-- Edit Soal Modal -->
                                        <div class="modal fade" id="editSoalModal{{ $q->soal_id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Soal</h5><button type="button"
                                                            class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.soal.update', ['examId' => $exam->ujian_id, 'soalId' => $q->soal_id]) }}"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="mb-3"><label class="form-label">Nama
                                                                    Soal</label><input type="text" name="nama_soal"
                                                                    class="form-control" value="{{ $q->nama_soal }}"
                                                                    required></div>
                                                            <div class="mb-3"><label class="form-label">File PDF Soal
                                                                    (Opsional)
                                                                </label><input type="file" name="soal_pdf"
                                                                    class="form-control" accept="application/pdf"></div>
                                                            <div class="mb-3"><label class="form-label">Bobot
                                                                    Nilai</label><input type="number" name="bobot_nilai"
                                                                    class="form-control" value="{{ $q->bobot_nilai }}"
                                                                    required min="0"></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Soal Modal -->
                                        <div class="modal fade" id="deleteSoalModal{{ $q->soal_id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Soal?</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.soal.destroy', ['examId' => $exam->ujian_id, 'soalId' => $q->soal_id]) }}">
                                                        @csrf @method('DELETE')
                                                        <div class="modal-body">Hapus soal
                                                            <strong>{{ $q->nama_soal }}</strong>? Semua test case di
                                                            dalamnya juga akan terhapus.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-start ps-4">Tidak ada soal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Table Peserta -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-warning me-2"></i> Peserta Ujian</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <form action="" method="GET" class="input-group input-group-sm shadow-sm rounded-pill overflow-hidden" style="max-width: 250px;">
                                <span class="input-group-text bg-white border-end-0 px-2"><i class="bi bi-search text-muted small"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 shadow-none px-0" placeholder="Cari..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary px-3">Cari</button>
                            </form>
                            <a href="{{ route('admin.ujian.export', $exam->ujian_id) }}" class="btn btn-sm btn-success rounded-pill shadow-sm px-3" title="Generate Laporan"><i class="bi bi-printer-fill"></i></a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="text-nowrap border-bottom border-warning border-2">
                                        <th class="ps-4 py-3">NIM</th>
                                        <th class="py-3">Nama Mahasiswa</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Skor</th>
                                        <th class="pe-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @forelse($participants as $p)
                                        <tr>
                                            <td class="ps-4">{{ $p->nim_nip }}</td>
                                            <td class="fw-semibold">{{ $p->name }}</td>
                                            <td>
                                                @if ($p->status == 'Lulus')
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i
                                                            class="bi bi-check-circle-fill me-1"></i> Lulus</span>
                                                @else
                                                    <span
                                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i
                                                            class="bi bi-x-circle-fill me-1"></i> Tidak Lulus</span>
                                                @endif

                                            </td>
                                            <td><strong>{{ $p->total_skor ?? 0 }}</strong> Pts</td>
                                            <td class="pe-4 text-center">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseSubmissions{{ $p->user_id }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapseSubmissions{{ $p->user_id }}"
                                                    onclick="this.querySelector('.bi-chevron-down').classList.toggle('rotate-180')">
                                                    Detail <i class="bi bi-chevron-down ms-1"
                                                        style="transition: transform 0.3s ease; display: inline-block;"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5" class="p-0 border-0">
                                                <div class="collapse" id="collapseSubmissions{{ $p->user_id }}">
                                                    <div
                                                        class="card card-body border-0 bg-light rounded-0 border-start border-4 border-primary m-3 shadow-sm">
                                                        <h6 class="fw-bold mb-3">Riwayat Submission</h6>
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>Waktu</th>
                                                                    <th>Soal</th>
                                                                    <th>Status</th>
                                                                    <th>Skor Asli</th>
                                                                    <th>Catatan</th>
                                                                    <th>Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($p->submissions as $s)
                                                                    <tr>
                                                                        <td class="text-nowrap">
                                                                            {{ \Carbon\Carbon::parse($s->created_at)->format('d M Y, H:i') }}
                                                                        </td>
                                                                        <td>{{ $s->nama_soal }}</td>
                                                                        <td>
                                                                            @if ($s->status == 'Accepted')
                                                                                <span
                                                                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Accepted</span>
                                                                            @elseif($s->status == 'Wrong Answer')
                                                                                <span
                                                                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Wrong
                                                                                    Answer</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">{{ $s->status }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $s->skor }}</td>
                                                                        <td>
                                                                            @if ($s->justification_note)
                                                                                <span class="text-muted small" title="{{ $s->justification_note }}">
                                                                                    {{ \Illuminate\Support\Str::limit($s->justification_note, 30) }}
                                                                                </span>
                                                                            @else
                                                                                <span class="text-muted small">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <button class="btn btn-sm btn-outline-secondary py-0 d-inline-flex align-items-center justify-content-center gap-1 text-nowrap" style="width: 95px;" data-bs-toggle="modal" data-bs-target="#codeModal{{ $s->submission_id }}">
                                                                                <i class="bi bi-code-slash"></i> Kode
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="text-center text-muted">
                                                                            Belum ada submission.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Modals for source code -->
                                                @foreach ($p->submissions as $s)
                                                    <div class="modal fade" id="codeModal{{ $s->submission_id }}"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                                            <div class="modal-content rounded-4 border-0 shadow">
                                                                <div class="modal-header border-0 pb-0">
                                                                    <h5 class="modal-title fw-bold"><i
                                                                            class="bi bi-file-earmark-code text-primary me-2"></i>Source
                                                                        Code</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <pre class="bg-dark text-light p-3 rounded mt-2" style="max-height: 400px; overflow-y: auto;"><code>{{ $s->source_code }}</code></pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada peserta yang mengikuti ujian ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 py-3 rounded-bottom-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-2">
                            <span class="text-muted small fw-semibold mb-2 mb-md-0">Menampilkan {{ $participants->firstItem() ?? 0 }} hingga {{ $participants->lastItem() ?? 0 }} dari {{ $participants->total() }} peserta</span>
                            <div class="m-0 pagination-sm">
                                {{ $participants->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Soal Baru</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.soal.store', $exam->ujian_id) }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nama Soal</label><input type="text"
                                name="nama_soal" class="form-control" placeholder="Contoh: Soal 1 - Hello World"
                                required></div>
                        <div class="mb-3"><label class="form-label">File PDF Soal</label><input type="file"
                                name="soal_pdf" class="form-control" accept="application/pdf" required></div>
                        <div class="mb-3"><label class="form-label">Bobot Nilai</label><input type="number"
                                name="bobot_nilai" class="form-control" placeholder="20" required min="0"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
