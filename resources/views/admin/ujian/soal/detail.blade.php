@extends('layouts.sidebar')

@section('title', 'Detail Soal')
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
    <!-- Back Button -->
    <a href="{{ route('admin.ujian.detail', ['id' => 1]) }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class="row">
        <!-- Left column: PDF placeholder -->
        <div class="col-md-6 mb-4">
            <h5 class="fw-bold mb-3">Soal PDF</h5>
            <div class="border rounded-3 p-3 text-center bg-light" style="height:400px;">
                <i class="bi bi-file-earmark-pdf display-4 text-danger"></i>
                <p class="mt-2">PDF placeholder for "{{ $question->name }}"</p>
            </div>
        </div>

        <!-- Right column: Test Cases -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-list-check text-success me-2"></i> Test Cases</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTestCaseModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Test Case
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-top">
                            <thead class="table-light"><tr class="text-nowrap">
                                <th>#</th><th>Input</th><th>Expected Output</th><th>Aksi</th>
                            </tr></thead>
                            <tbody>
                                @forelse($testCases as $tc)
                                <tr>
                                    <td>{{ $tc['id'] }}</td>
                                    <td>{{ $tc['input'] }}</td>
                                    <td>{{ $tc['expected_output'] }}</td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editTestCaseModal{{ $tc['id'] }}"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteTestCaseModal{{ $tc['id'] }}"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>

                                <!-- Edit Test Case Modal (dummy) -->
                                <div class="modal fade" id="editTestCaseModal{{ $tc['id'] }}" tabindex="-1" aria-labelledby="editTestCaseModalLabel{{ $tc['id'] }}" aria-hidden="true">
                                  <div class="modal-dialog"><div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title" id="editTestCaseModalLabel{{ $tc['id'] }}">Edit Test Case</h5></div>
                                    <div class="modal-body">
                                      <form>
                                        <div class="mb-3"><label class="form-label">Input</label><textarea class="form-control" rows="2">{{ $tc['input'] }}</textarea></div>
                                        <div class="mb-3"><label class="form-label">Expected Output</label><textarea class="form-control" rows="2">{{ $tc['expected_output'] }}</textarea></div>
                                      </form>
                                    </div>
                                    <div class="modal-footer">
                                      <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                      <button class="btn btn-primary">Simpan</button>
                                    </div>
                                  </div></div>
                                </div>

                                <!-- Delete Test Case Confirmation -->
                                <div class="modal fade" id="deleteTestCaseModal{{ $tc['id'] }}" tabindex="-1" aria-labelledby="deleteTestCaseModalLabel{{ $tc['id'] }}" aria-hidden="true">
                                  <div class="modal-dialog"><div class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title" id="deleteTestCaseModalLabel{{ $tc['id'] }}">Hapus Test Case?</h5></div>
                                    <div class="modal-body">Apakah Anda yakin ingin menghapus test case #{{ $tc['id'] }}?</div>
                                    <div class="modal-footer">
                                      <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                      <button class="btn btn-danger">Hapus</button>
                                    </div>
                                  </div></div>
                                </div>

                                @empty
                                <tr><td colspan="4" class="text-start">Tidak ada test case.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Test Case Modal -->
<div class="modal fade" id="addTestCaseModal" tabindex="-1" aria-labelledby="addTestCaseModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="addTestCaseModalLabel">Tambah Test Case</h5></div>
    <div class="modal-body">
      <form>
        <div class="mb-3"><label class="form-label">Input</label><textarea class="form-control" rows="2"></textarea></div>
        <div class="mb-3"><label class="form-label">Expected Output</label><textarea class="form-control" rows="2"></textarea></div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </div></div>
</div>
@endsection
