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

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left column: PDF Viewer -->
            <div class="col-md-6 mb-4">
                <h5 class="fw-bold mb-3">{{ $question->nama_soal }}</h5>
                @if ($question->soal_pdf)
                    <div class="pdf-viewer-container border rounded-4 bg-light overflow-hidden d-flex flex-column" style="height: 500px;">
                        <!-- Toolbar -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-2 border-bottom gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <span class="small mx-2 fw-semibold" style="font-size: 0.85rem;">
                                    Total: <span id="page-count" class="fw-semibold">0</span> Halaman
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" id="zoom-out" class="btn btn-sm btn-light border py-1 px-2">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <span id="zoom-percent" class="small mx-1" style="font-size: 0.85rem;">100%</span>
                                <button type="button" id="zoom-in" class="btn btn-sm btn-light border py-1 px-2">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <a href="{{ asset('storage/' . $question->soal_pdf) }}" target="_blank" class="btn btn-sm btn-light border py-1 px-2 ms-2" title="Buka di tab baru">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Render Area -->
                        <div class="flex-grow-1 overflow-auto p-3 d-flex flex-column align-items-center bg-secondary bg-opacity-10" id="pdf-scroll-container">
                            <!-- Canvases generated dynamically -->
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const url = '{{ asset('storage/' . $question->soal_pdf) }}';
                        let pdfDoc = null;
                        const scaleStep = 0.2;
                        let scale = 1.0;

                        const container = document.getElementById('pdf-scroll-container');

                        const renderAllPages = () => {
                            container.innerHTML = '';

                            for (let num = 1; num <= pdfDoc.numPages; num++) {
                                const canvas = document.createElement('canvas');
                                canvas.id = 'pdf-canvas-' + num;
                                canvas.className = 'shadow-sm bg-white rounded mb-3 d-block mx-auto';
                                canvas.style.height = 'auto';
                                container.appendChild(canvas);

                                const ctx = canvas.getContext('2d');

                                pdfDoc.getPage(num).then(page => {
                                    const dpr = window.devicePixelRatio || 1;
                                    // Render at high resolution (min scale 1.5) to keep text sharp when zoomed out
                                    const renderScale = Math.max(scale, 1.5);
                                    const renderViewport = page.getViewport({ scale: renderScale });
                                    
                                    canvas.width = renderViewport.width * dpr;
                                    canvas.height = renderViewport.height * dpr;

                                    // Set responsive percentage width based on scale zoom factor
                                    canvas.style.width = (scale * 100) + '%';
                                    canvas.style.height = 'auto';

                                    ctx.scale(dpr, dpr);

                                    const renderCtx = {
                                        canvasContext: ctx,
                                        viewport: renderViewport
                                    };

                                    page.render(renderCtx);
                                });
                            }
                            
                            document.getElementById('zoom-percent').textContent = Math.round(scale * 100) + '%';
                        };

                        pdfjsLib.getDocument(url).promise.then(pdfDoc_ => {
                            pdfDoc = pdfDoc_;
                            document.getElementById('page-count').textContent = pdfDoc.numPages;
                            renderAllPages();
                        }).catch(err => {
                            console.error('PDF.js Error loading PDF:', err);
                            container.innerHTML = `<div class="alert alert-danger m-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>Gagal memuat file PDF soal.</div>`;
                        });

                        document.getElementById('zoom-in').addEventListener('click', () => {
                            scale = Math.min(scale + scaleStep, 2.5);
                            renderAllPages();
                        });

                        document.getElementById('zoom-out').addEventListener('click', () => {
                            scale = Math.max(scale - scaleStep, 0.3);
                            renderAllPages();
                        });
                    });
                    </script>
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
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-3 d-flex justify-content-between align-items-center gap-3 gap-md-4">
                        <h5 class="fw-bold mb-0 text-nowrap flex-shrink-0"><i class="bi bi-list-check text-success me-2"></i> Test Cases</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-2 px-md-3 shadow-sm d-flex align-items-center ms-auto text-nowrap" data-bs-toggle="modal" data-bs-target="#addTestCaseModal">
                            <i class="bi bi-plus-circle"></i>
                            <span class="d-none d-md-inline ms-1">Tambah Test Case</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered font-monospace small">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%" class="text-center">No</th>
                                        <th width="35%">Input (stdin)</th>
                                        <th width="35%">Expected Output (stdout)</th>
                                        <th width="20%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($testCases as $index => $tc)
                                        <tr>
                                            <td class="text-center align-top pt-3">{{ $index + 1 }}</td>
                                            <td>
                                                <pre class="mb-0 p-2 bg-light rounded border">{{ $tc->input }}</pre>
                                            </td>
                                            <td>
                                                <pre class="mb-0 p-2 bg-light rounded border">{{ $tc->expected_output }}</pre>
                                            </td>
                                            <td class="text-nowrap align-top text-center pt-3">
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editTestCaseModal{{ $tc->test_case_id }}"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteTestCaseModal{{ $tc->test_case_id }}"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>

                                        @push('modals')
                                        <!-- Edit Test Case Modal -->
                                        <div class="modal fade" id="editTestCaseModal{{ $tc->test_case_id }}"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Test Case</h5><button type="button"
                                                            class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.testcase.update', [$exam->ujian_id, $question->soal_id, $tc->test_case_id]) }}">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3"><label class="form-label">Input</label>
                                                                <textarea name="input" class="form-control" rows="3" required>{{ $tc->input }}</textarea>
                                                            </div>
                                                            <div class="mb-3"><label class="form-label">Expected
                                                                    Output</label>
                                                                <textarea name="expected_output" class="form-control" rows="3" required>{{ $tc->expected_output }}</textarea>
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

                                        <!-- Delete Test Case Modal -->
                                        <div class="modal fade" id="deleteTestCaseModal{{ $tc->test_case_id }}"
                                            tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Test Case?</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.testcase.destroy', [$exam->ujian_id, $question->soal_id, $tc->test_case_id]) }}">
                                                        @csrf @method('DELETE')
                                                        <div class="modal-body">Hapus test case No {{ $index + 1 }}?
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
                                        @endpush
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                Belum ada test case untuk soal ini.
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

    @push('modals')
    <!-- Add Test Case Modal -->
    <div class="modal fade" id="addTestCaseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Test Case</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('admin.testcase.store', [$exam->ujian_id, $question->soal_id]) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Input</label>
                            <textarea name="input" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">Expected Output</label>
                            <textarea name="expected_output" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush
@endsection
