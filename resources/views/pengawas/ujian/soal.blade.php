@extends('layouts.sidebar')

@section('title', 'Detail Soal & Testcase')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Back Button -->
        <a href="{{ route('pengawas.ujian.detail', $exam->ujian_id) }}" class="btn btn-sm btn-back mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h3 class="fw-bold text-unsulbar mb-1">{{ $soal->nama_soal }}</h3>
                <p class="text-muted mb-0">{{ $exam->judul }} | Bobot: {{ $soal->bobot_nilai }}</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Kolom Kiri: PDF Soal -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Dokumen Soal (PDF)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if ($soal->soal_pdf)
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
                                         <a href="{{ asset('storage/' . $soal->soal_pdf) }}" target="_blank" class="btn btn-sm btn-light border py-1 px-2 ms-2" title="Buka di tab baru">
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
                                  const url = '{{ asset('storage/' . $soal->soal_pdf) }}';
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
                            <div class="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted"
                                style="min-height: 500px;">
                                <i class="bi bi-file-pdf fs-1 mb-2"></i>
                                <p class="mb-0">Belum ada file PDF</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Test Cases -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-braces text-success me-2"></i> Daftar Test Case</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered font-monospace small">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%">No</th>
                                        <th width="45%">Input (stdin)</th>
                                        <th width="45%">Expected Output (stdout)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($soal->testCases as $index => $tc)
                                        <tr>
                                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                                            <td>
                                                <pre class="mb-0 p-2 bg-light rounded border">{{ $tc->input }}</pre>
                                            </td>
                                            <td>
                                                <pre class="mb-0 p-2 bg-light rounded border">{{ $tc->expected_output }}</pre>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5 text-muted">
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
@endsection
