@extends('layouts.sidebar')

@section('title', 'Manajemen Ujian & Soal')

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
<div class="container-fluid py-4">
    <!-- Card Wrapper -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-card-list text-primary me-2"></i> Daftar Ujian</h5>
            <!-- Add Exam Button -->
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExamModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Ujian
            </button>
        </div>
        <div class="card-body">
            <!-- Exams Table -->
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead class="table-light">
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Durasi (menit)</th>
                            <th>Passing Grade (%)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>{{ $exam['id'] }}</td>
                                <td>{{ $exam['title'] }}</td>
                                <td>{{ $exam['description'] }}</td>
                                <td>{{ $exam['duration'] }}</td>
                                <td>{{ $exam['passing_grade'] }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.ujian.detail', $exam['id']) }}" class="btn btn-sm btn-outline-primary me-1">
                                        Detail
                                    </a>
                                    <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editExamModal{{ $exam['id'] }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteExamModal{{ $exam['id'] }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-start">Tidak ada ujian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Exam Modal -->
<div class="modal fade" id="addExamModal" tabindex="-1" aria-labelledby="addExamModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addExamModalLabel">Tambah Ujian Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" placeholder="Judul Ujian">
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" rows="3" placeholder="Deskripsi singkat"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Durasi (menit)</label>
            <input type="number" class="form-control" placeholder="Durasi">
          </div>
          <div class="mb-3">
            <label class="form-label">Passing Grade (%)</label>
            <input type="number" class="form-control" placeholder="Passing Grade">
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
