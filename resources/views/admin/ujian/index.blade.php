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
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExamModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Ujian
                </button>
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
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exams as $index => $exam)
                                <tr>
                                    <td>{{ $exams->firstItem() + $index }}</td>
                                    <td>{{ $exam->judul }}</td>
                                    <td>{{ Str::limit($exam->deskripsi, 50) }}</td>
                                    <td>{{ $exam->durasi }}</td>
                                    <td>{{ $exam->passing_grade }}</td>
                                    <td>
                                        @if ($exam->status === 'active')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill"><i
                                                    class="bi bi-circle-fill small me-1"></i>Berjalan</span>
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
                                    <td colspan="7" class="text-start">Tidak ada ujian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">{{ $exams->links() }}</div>
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
