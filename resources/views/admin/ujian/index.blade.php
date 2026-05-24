@extends('layouts.sidebar')

@section('title', 'Manajemen Ujian & Soal')

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
    <div class="container-fluid py-4">

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

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-card-list text-primary me-2"></i> Daftar Ujian</h5>
                <div class="d-flex gap-2">
                    <input type="text" id="searchUjianInput" class="form-control form-control-sm search-input rounded-pill"
                        placeholder="Cari Judul Ujian...">
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#addExamModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Ujian
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-top">
                        <thead class="table-light">
                            <tr class="text-nowrap">
                                <th>No</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Durasi (menit)</th>
                                <th>Passing Grade (Pts)</th>
                                <th>Maks. Submit</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ujianTableBody">
                            @forelse($exams as $index => $exam)
                                <tr class="ujian-row" data-judul="{{ $exam->judul }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $exam->judul }}</td>
                                    <td>{{ Str::limit($exam->deskripsi, 50) }}</td>
                                    <td>{{ $exam->durasi }}</td>
                                    <td>{{ $exam->passing_grade }}</td>
                                    <td>{{ $exam->max_attempt }}</td>
                                    <td>
                                        @if ($exam->status === 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i
                                                    class="bi bi-play-circle-fill small me-1"></i>Berjalan</span>
                                        @elseif ($exam->status === 'finished')
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill"><i
                                                    class="bi bi-check-circle-fill small me-1"></i>Selesai</span>
                                        @else
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill"><i
                                                    class="bi bi-pause-circle-fill small me-1"></i>Belum Dimulai</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.ujian.detail', $exam->ujian_id) }}"
                                            class="btn btn-sm btn-outline-primary me-1">Detail</a>
                                        <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                            data-bs-target="#editExamModal{{ $exam->ujian_id }}"><i
                                                class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteExamModal{{ $exam->ujian_id }}"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>

                                <!-- Edit Exam Modal -->
                                <div class="modal fade" id="editExamModal{{ $exam->ujian_id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Ujian</h5><button type="button"
                                                    class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST"
                                                action="{{ route('admin.ujian.update', $exam->ujian_id) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3"><label class="form-label">Judul</label><input
                                                            type="text" name="judul" class="form-control"
                                                            value="{{ $exam->judul }}" required></div>
                                                    <div class="mb-3"><label class="form-label">Deskripsi</label>
                                                        <textarea name="deskripsi" class="form-control" rows="3">{{ $exam->deskripsi }}</textarea>
                                                    </div>
                                                    <div class="mb-3"><label class="form-label">Durasi
                                                            (menit)
                                                        </label><input type="number" name="durasi" class="form-control"
                                                            value="{{ $exam->durasi }}" required min="1"></div>
                                                    <div class="mb-3"><label class="form-label">Passing Grade
                                                            (Pts)</label><input type="number" name="passing_grade"
                                                            class="form-control" value="{{ $exam->passing_grade }}"
                                                            required min="0"></div>
                                                    <div class="mb-3"><label class="form-label">Maks. Submit per Soal</label><input type="number" name="max_attempt"
                                                            class="form-control" value="{{ $exam->max_attempt }}"
                                                            required min="1"></div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select">
                                                            <option value="closed"
                                                                {{ $exam->status === 'closed' ? 'selected' : '' }}>Belum
                                                                Dimulai</option>
                                                            <option value="active"
                                                                {{ $exam->status === 'active' ? 'selected' : '' }}>Berjalan
                                                            </option>
                                                            <option value="finished"
                                                                {{ $exam->status === 'finished' ? 'selected' : '' }}>
                                                                Selesai</option>
                                                        </select>
                                                    </div>
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

                                <!-- Delete Exam Modal -->
                                <div class="modal fade" id="deleteExamModal{{ $exam->ujian_id }}" tabindex="-1"
                                    aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Hapus Ujian?</h5>
                                            </div>
                                            <form method="POST"
                                                action="{{ route('admin.ujian.destroy', $exam->ujian_id) }}">
                                                @csrf @method('DELETE')
                                                <div class="modal-body">Hapus ujian <strong>{{ $exam->judul }}</strong>?
                                                    Semua soal dan test case akan ikut terhapus.</div>
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
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        Belum ada ujian yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="ujianTablePagination" class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 px-2 pb-3"></div>
            </div>
        </div>
    </div>

    <!-- Add Exam Modal -->
    <div class="modal fade" id="addExamModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Ujian Baru</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.ujian.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Judul</label><input type="text" name="judul"
                                class="form-control" placeholder="Judul Ujian" required></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat"></textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">Durasi (menit)</label><input type="number"
                                name="durasi" class="form-control" placeholder="60" required min="1"></div>
                        <div class="mb-3"><label class="form-label">Passing Grade (Pts)</label><input type="number"
                                name="passing_grade" class="form-control" placeholder="70" required min="0"></div>
                        <div class="mb-3"><label class="form-label">Maks. Submit per Soal</label><input type="number"
                                name="max_attempt" class="form-control" placeholder="3" required min="1" value="3"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchUjianInput');
            const tableBody = document.getElementById('ujianTableBody');
            const paginationContainer = document.getElementById('ujianTablePagination');
            
            if (!tableBody || !paginationContainer) return;

            const allRows = Array.from(tableBody.querySelectorAll('.ujian-row'));
            const rowsPerPage = 10;
            let currentPage = 1;
            let currentSearchTerm = '';

            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    currentSearchTerm = e.target.value.toLowerCase();
                    currentPage = 1; // Reset ke halaman 1 saat mencari
                    updateTable();
                });
            }

            function updateTable() {
                const filteredRows = allRows.filter(row => {
                    const judul = (row.getAttribute('data-judul') || '').toLowerCase();
                    return judul.includes(currentSearchTerm);
                });

                const totalRows = filteredRows.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage) || 1;
                if (currentPage > totalPages) currentPage = totalPages;

                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;

                allRows.forEach(row => { row.style.display = 'none'; });

                filteredRows.forEach((row, index) => {
                    if (index >= start && index < end) {
                        row.style.display = '';
                    }
                });

                renderPagination(totalRows, totalPages);
            }

            function renderPagination(totalRows, totalPages) {
                paginationContainer.innerHTML = '';
                if (totalRows === 0) {
                    const emptySpan = document.createElement('span');
                    emptySpan.className = 'text-muted small fw-semibold';
                    emptySpan.innerText = 'Tidak ada ujian yang ditemukan';
                    paginationContainer.appendChild(emptySpan);
                    return;
                }

                const startItem = (currentPage - 1) * rowsPerPage + 1;
                const endItem = Math.min(currentPage * rowsPerPage, totalRows);

                const summarySpan = document.createElement('span');
                summarySpan.className = 'text-muted small fw-semibold mb-2 mb-md-0';
                summarySpan.innerText = `Menampilkan ${startItem} hingga ${endItem} dari ${totalRows} ujian`;
                paginationContainer.appendChild(summarySpan);

                if (totalPages <= 1) return;

                const nav = document.createElement('nav');
                const ul = document.createElement('ul');
                ul.className = 'pagination pagination-sm mb-0 shadow-sm';

                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link px-3 rounded-start-pill" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                prevLi.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage > 1) { currentPage--; updateTable(); }
                };
                ul.appendChild(prevLi);

                for (let i = 1; i <= totalPages; i++) {
                    const li = document.createElement('li');
                    li.className = `page-item ${currentPage === i ? 'active' : ''}`;
                    li.innerHTML = `<a class="page-link px-3" href="#">${i}</a>`;
                    li.onclick = (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateTable();
                    };
                    ul.appendChild(li);
                }

                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link px-3 rounded-end-pill" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                nextLi.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) { currentPage++; updateTable(); }
                };
                ul.appendChild(nextLi);

                nav.appendChild(ul);
                paginationContainer.appendChild(nav);
            }

            updateTable();
        });
    </script>
@endsection
