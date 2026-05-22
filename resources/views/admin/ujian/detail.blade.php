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

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
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
                <div class="card border-0 shadow-sm rounded-4">
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
                                        <th class="ps-4 py-3">No</th>
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
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada soal untuk ujian ini.
                                            </td>
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
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-warning me-2"></i> Peserta Ujian</h5>
                        <div class="d-flex flex-column flex-md-row gap-2">
                            <input type="text" id="searchParticipantInput" class="form-control form-control-sm search-input rounded-pill" style="max-width: 250px;" placeholder="Cari peserta (NIM/Nama)...">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm dropdown-toggle d-flex align-items-center gap-1 h-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-funnel-fill"></i> <span id="activeFilterText">Urutkan: Normal</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow rounded-4 border-0" aria-labelledby="filterDropdown">
                                    <li><a class="dropdown-item filter-option active" href="#" data-sort="normal"><i class="bi bi-sort-numeric-down me-2 text-primary"></i> Normal (Skor Tertinggi)</a></li>
                                    <li><a class="dropdown-item filter-option" href="#" data-sort="terbaru"><i class="bi bi-clock-history me-2 text-success"></i> Submission Terakhir</a></li>
                                    <li><a class="dropdown-item filter-option" href="#" data-sort="terlama"><i class="bi bi-clock me-2 text-warning"></i> Submission Terlama</a></li>
                                </ul>
                            </div>
                            <a href="{{ route('admin.ujian.export', $exam->ujian_id) }}"
                                class="btn btn-sm btn-success rounded-pill shadow-sm px-3 d-flex align-items-center justify-content-center" title="Generate Laporan"><i
                                    class="bi bi-printer-fill"></i></a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="participantTable" class="table table-hover align-middle mb-0">
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
                                        @php
                                            $latestSub = $p->submissions->sortByDesc('created_at')->first();
                                            $latestTime = $latestSub ? \Carbon\Carbon::parse($latestSub->created_at)->timestamp : 0;
                                        @endphp
                                        <tr class="participant-row" data-nim="{{ $p->nim_username }}" data-nama="{{ $p->name }}" data-skor="{{ $p->total_skor ?? 0 }}" data-waktu="{{ $latestTime }}">
                                            <td class="ps-4">{{ $p->nim_username }}</td>
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
                                                         <div class="d-flex justify-content-between align-items-center mb-3">
                                                             <h6 class="fw-bold mb-0">Riwayat Submission</h6>
                                                             <select class="form-select form-select-sm w-auto" onchange="window.filterSubmissions('{{ $p->user_id }}', this.value)">
                                                                 <option value="">Semua Soal</option>
                                                                 @foreach($exam->soals as $soal)
                                                                     <option value="{{ $soal->soal_id }}">{{ $soal->nama_soal }}</option>
                                                                 @endforeach
                                                             </select>
                                                         </div>
                                                         <table id="table-sub-{{ $p->user_id }}"
                                                             class="table table-sm table-bordered mb-0 submission-table">
                                                            <thead class="table-secondary">
                                                                <tr>
                                                                    <th>Waktu</th>
                                                                    <th>Soal</th>
                                                                    <th>Status</th>
                                                                    <th>Skor</th>
                                                                    <th>Catatan</th>
                                                                    <th>Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($p->submissions as $s)
                                                                    <tr class="submission-row" data-soal="{{ $s->soal_id }}">
                                                                        <td class="text-nowrap">
                                                                            {{ \Carbon\Carbon::parse($s->created_at)->format('d M Y, H:i') }}
                                                                        </td>
                                                                        <td>{{ $s->nama_soal }}</td>
                                                                        <td>
                                                                            @if ($s->status == 'Accepted')
                                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Accepted</span>
                                                                            @elseif($s->status == 'Wrong Answer')
                                                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Wrong Answer</span>
                                                                            @elseif($s->status == 'Time Limit Exceeded')
                                                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Time Limit Exceeded</span>
                                                                            @elseif($s->status == 'Compilation Error')
                                                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Compilation Error</span>
                                                                            @elseif($s->status == 'Runtime Error')
                                                                                <span class="badge border border-opacity-25" style="color: #a855f7; background-color: rgba(168,85,247,0.15); border-color: rgba(168,85,247,0.3);">Runtime Error</span>
                                                                            @else
                                                                                <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25">{{ $s->status }}</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $s->skor }}</td>
                                                                        <td>
                                                                            @if ($s->justification_note)
                                                                                <span class="text-muted small"
                                                                                    title="{{ $s->justification_note }}">
                                                                                    {{ \Illuminate\Support\Str::limit($s->justification_note, 30) }}
                                                                                </span>
                                                                            @else
                                                                                <span class="text-muted small">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <button
                                                                                class="btn btn-sm btn-outline-secondary py-0 d-inline-flex align-items-center justify-content-center gap-1 text-nowrap"
                                                                                style="width: 95px;"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#codeModal{{ $s->submission_id }}">
                                                                                <i class="bi bi-code-slash"></i> Kode
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr class="no-submissions-row">
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.paginateTable = function(tableId, rowsPerPage, filterSoalId = '') {
                const table = document.getElementById(tableId);
                if (!table) return;
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                // Hapus container pagination yang sudah ada jika ada
                const existingPagination = document.getElementById(tableId + '-pagination');
                if (existingPagination) {
                    existingPagination.remove();
                }

                const allRows = Array.from(tbody.querySelectorAll('.submission-row'));
                const noSubRow = tbody.querySelector('.no-submissions-row');

                if (noSubRow) {
                    noSubRow.style.display = '';
                    return;
                }

                // Filter baris
                const rows = allRows.filter(row => {
                    if (filterSoalId === '' || row.getAttribute('data-soal') === filterSoalId) {
                        return true;
                    }
                    row.style.display = 'none';
                    return false;
                });

                // Hapus empty state row jika ada
                const existingEmptyRow = tbody.querySelector('.empty-state-row');
                if (existingEmptyRow) {
                    existingEmptyRow.remove();
                }

                const totalRows = rows.length;
                if (totalRows === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.className = 'empty-state-row';
                    const colCount = table.querySelector('thead tr').children.length;
                    emptyRow.innerHTML = `<td colspan="${colCount}" class="text-center text-muted">Tidak ada submission untuk soal ini.</td>`;
                    tbody.appendChild(emptyRow);
                    return;
                }

                let currentPage = 1;

                if (totalRows <= rowsPerPage) {
                    rows.forEach(row => {
                        row.style.display = '';
                    });
                    // Sembunyikan baris lain yang tidak termasuk filter
                    allRows.forEach(row => {
                        if (!rows.includes(row)) {
                            row.style.display = 'none';
                        }
                    });
                    return;
                }

                const totalPages = Math.ceil(totalRows / rowsPerPage);

                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'd-flex flex-column flex-md-row justify-content-between align-items-center mt-2 px-2';
                paginationContainer.id = tableId + '-pagination';
                table.parentNode.insertBefore(paginationContainer, table.nextSibling);

                function renderTable() {
                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;
                    rows.forEach((row, index) => {
                        row.style.display = (index >= start && index < end) ? '' : 'none';
                    });
                    allRows.forEach(row => {
                        if (!rows.includes(row)) {
                            row.style.display = 'none';
                        }
                    });
                    renderPagination();
                }

                function renderPagination() {
                    paginationContainer.innerHTML = '';
                    const startItem = (currentPage - 1) * rowsPerPage + 1;
                    const endItem = Math.min(currentPage * rowsPerPage, totalRows);

                    const summarySpan = document.createElement('span');
                    summarySpan.className = 'text-muted small fw-semibold mb-2 mb-md-0';
                    summarySpan.innerText = `Menampilkan ${startItem} hingga ${endItem} dari ${totalRows} submisi`;
                    paginationContainer.appendChild(summarySpan);

                    const nav = document.createElement('nav');
                    const ul = document.createElement('ul');
                    ul.className = 'pagination pagination-sm mb-0';

                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                    prevLi.innerHTML =
                        `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                    prevLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage > 1) {
                            currentPage--;
                            renderTable();
                        }
                    };
                    ul.appendChild(prevLi);

                    for (let i = 1; i <= totalPages; i++) {
                        const pageLi = document.createElement('li');
                        pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageLi.onclick = (e) => {
                            e.preventDefault();
                            currentPage = i;
                            renderTable();
                        };
                        ul.appendChild(pageLi);
                    }

                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                    nextLi.innerHTML =
                        `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                    nextLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage < totalPages) {
                            currentPage++;
                            renderTable();
                        }
                    };
                    ul.appendChild(nextLi);

                    nav.appendChild(ul);
                    paginationContainer.appendChild(nav);
                }

                renderTable();
            }

            window.filterSubmissions = function(userId, soalId) {
                window.paginateTable('table-sub-' + userId, 5, soalId);
            }

            document.querySelectorAll('.submission-table').forEach(table => {
                window.paginateTable(table.id, 5);
            });

            function initParticipantTablePagination() {
                const searchInput = document.getElementById('searchParticipantInput');
                const table = document.getElementById('participantTable');
                if (!table) return;
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const allRows = Array.from(tbody.querySelectorAll('.participant-row')).map(row => {
                    return {
                        mainRow: row,
                        collapseRow: row.nextElementSibling,
                        skor: parseFloat(row.getAttribute('data-skor')) || 0,
                        waktu: parseInt(row.getAttribute('data-waktu')) || 0,
                        nim: (row.getAttribute('data-nim') || '').toLowerCase(),
                        nama: (row.getAttribute('data-nama') || '').toLowerCase()
                    };
                });

                const rowsPerPage = 10;
                let currentPage = 1;
                let currentSearchTerm = '';
                let currentSort = 'normal';

                const paginationContainer = document.createElement('div');
                paginationContainer.className = 'd-flex flex-column flex-md-row justify-content-between align-items-center mt-3 px-3 pb-3';
                paginationContainer.id = 'participantTable-pagination';
                table.parentNode.insertBefore(paginationContainer, table.nextSibling);

                function updateTable() {
                    let filteredItems = allRows.filter(item => {
                        return item.nim.includes(currentSearchTerm) || item.nama.includes(currentSearchTerm);
                    });

                    filteredItems.sort((a, b) => {
                        if (currentSort === 'terbaru') {
                            return b.waktu - a.waktu;
                        } else if (currentSort === 'terlama') {
                            return a.waktu - b.waktu;
                        } else {
                            return b.skor - a.skor;
                        }
                    });

                    // Re-append to tbody to reflect sorted order in DOM
                    filteredItems.forEach(item => {
                        tbody.appendChild(item.mainRow);
                        if (item.collapseRow) tbody.appendChild(item.collapseRow);
                    });

                    const totalRows = filteredItems.length;
                    const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
                    if (currentPage > totalPages) currentPage = totalPages;

                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;

                    allRows.forEach(item => { 
                        item.mainRow.style.display = 'none'; 
                        if (item.collapseRow) item.collapseRow.style.display = 'none';
                    });

                    filteredItems.forEach((item, index) => {
                        if (index >= start && index < end) {
                            item.mainRow.style.display = '';
                            if (item.collapseRow) item.collapseRow.style.display = '';
                        }
                    });

                    renderPagination(totalRows, totalPages);
                }

                function renderPagination(totalRows, totalPages) {
                    paginationContainer.innerHTML = '';
                    if (totalRows === 0) {
                        const emptySpan = document.createElement('span');
                        emptySpan.className = 'text-muted small fw-semibold';
                        emptySpan.innerText = 'Tidak ada peserta yang ditemukan';
                        paginationContainer.appendChild(emptySpan);
                        return;
                    }

                    const startItem = (currentPage - 1) * rowsPerPage + 1;
                    const endItem = Math.min(currentPage * rowsPerPage, totalRows);

                    const summarySpan = document.createElement('span');
                    summarySpan.className = 'text-muted small fw-semibold mb-2 mb-md-0';
                    summarySpan.innerText = `Menampilkan ${startItem} hingga ${endItem} dari ${totalRows} peserta`;
                    paginationContainer.appendChild(summarySpan);

                    if (totalPages <= 1) return;

                    const nav = document.createElement('nav');
                    const ul = document.createElement('ul');
                    ul.className = 'pagination pagination-sm mb-0';

                    const prevLi = document.createElement('li');
                    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                    prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                    prevLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage > 1) { currentPage--; updateTable(); }
                    };
                    ul.appendChild(prevLi);

                    for (let i = 1; i <= totalPages; i++) {
                        const pageLi = document.createElement('li');
                        pageLi.className = `page-item ${currentPage === i ? 'active' : ''}`;
                        pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                        pageLi.onclick = (e) => {
                            e.preventDefault();
                            currentPage = i;
                            updateTable();
                        };
                        ul.appendChild(pageLi);
                    }

                    const nextLi = document.createElement('li');
                    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                    nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                    nextLi.onclick = (e) => {
                        e.preventDefault();
                        if (currentPage < totalPages) { currentPage++; updateTable(); }
                    };
                    ul.appendChild(nextLi);

                    nav.appendChild(ul);
                    paginationContainer.appendChild(nav);
                }

                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        currentSearchTerm = this.value.toLowerCase();
                        currentPage = 1;
                        updateTable();
                    });
                }

                document.querySelectorAll('.filter-option').forEach(option => {
                    option.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.querySelectorAll('.filter-option').forEach(opt => opt.classList.remove('active'));
                        this.classList.add('active');
                        currentSort = this.getAttribute('data-sort');
                        const activeText = document.getElementById('activeFilterText');
                        if (activeText) {
                            if (currentSort === 'terbaru') activeText.innerText = 'Urutkan: Terbaru';
                            else if (currentSort === 'terlama') activeText.innerText = 'Urutkan: Terlama';
                            else activeText.innerText = 'Urutkan: Normal';
                        }
                        currentPage = 1;
                        updateTable();
                    });
                });

                updateTable();
            }

            initParticipantTablePagination();
        });
    </script>

    <style>
        /* Sembunyikan teks bahasa Inggris bawaan Laravel dari pagination */
        .pagination-sm nav div.d-sm-flex > div:first-child,
        .pagination-sm p.small.text-muted {
            display: none !important;
        }
    </style>
@endsection
