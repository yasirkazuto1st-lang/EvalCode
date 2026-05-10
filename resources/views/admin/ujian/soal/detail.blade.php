@extends('layouts.sidebar')

@section('title', 'Detail Soal')
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
        <!-- Back Button -->
        <a href="{{ route('admin.ujian.detail', ['id' => $exam->ujian_id]) }}" class="btn btn-sm btn-back mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left column: PDF Viewer -->
            <div class="col-md-6 mb-4">
                <h5 class="fw-bold mb-3">{{ $question->nama_soal }}</h5>
                @if($question->soal_pdf)
                    <iframe src="{{ asset('storage/' . $question->soal_pdf) }}" width="100%" height="500" style="border: 1px solid #dee2e6; border-radius: 12px;"></iframe>
                @else
                    <div class="border rounded-3 p-3 text-center bg-light" style="height:400px;">
                        <i class="bi bi-file-earmark-pdf display-4 text-danger"></i>
                        <p class="mt-2">Belum ada file PDF untuk soal ini.</p>
                    </div>
                @endif
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
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th>#</th><th>Input</th><th>Expected Output</th><th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($testCases as $index => $tc)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><code>{{ $tc->input }}</code></td>
                                            <td><code>{{ $tc->expected_output }}</code></td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editTestCaseModal{{ $tc->test_case_id }}"><i class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteTestCaseModal{{ $tc->test_case_id }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>

                                        <!-- Edit Test Case Modal -->
                                        <div class="modal fade" id="editTestCaseModal{{ $tc->test_case_id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog"><div class="modal-content">
                                                <div class="modal-header"><h5 class="modal-title">Edit Test Case</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                <form method="POST" action="{{ route('admin.testcase.update', [$exam->ujian_id, $question->soal_id, $tc->test_case_id]) }}">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3"><label class="form-label">Input</label><textarea name="input" class="form-control" rows="3" required>{{ $tc->input }}</textarea></div>
                                                    <div class="mb-3"><label class="form-label">Expected Output</label><textarea name="expected_output" class="form-control" rows="3" required>{{ $tc->expected_output }}</textarea></div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                                </form>
                                            </div></div>
                                        </div>

                                        <!-- Delete Test Case Modal -->
                                        <div class="modal fade" id="deleteTestCaseModal{{ $tc->test_case_id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog"><div class="modal-content">
                                                <div class="modal-header"><h5 class="modal-title">Hapus Test Case?</h5></div>
                                                <form method="POST" action="{{ route('admin.testcase.destroy', [$exam->ujian_id, $question->soal_id, $tc->test_case_id]) }}">
                                                @csrf @method('DELETE')
                                                <div class="modal-body">Hapus test case #{{ $index + 1 }}?</div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                                </div>
                                                </form>
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
    <div class="modal fade" id="addTestCaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Tambah Test Case</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('admin.testcase.store', [$exam->ujian_id, $question->soal_id]) }}">
            @csrf
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Input</label><textarea name="input" class="form-control" rows="3" required></textarea></div>
                <div class="mb-3"><label class="form-label">Expected Output</label><textarea name="expected_output" class="form-control" rows="3" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            </form>
        </div></div>
    </div>
@endsection
