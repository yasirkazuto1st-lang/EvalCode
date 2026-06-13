@extends('layouts.sidebar')

@section('title', 'Dashboard Pengawas')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php $allExams = $activeExams->merge($closedExams); @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-journal-check text-unsulbar me-2"></i>Daftar Ujian</h4>
        <div id="pagination-daftar-ujian"></div>
    </div>
    <div class="row g-4 mb-5" id="container-daftar-ujian">
        @forelse($allExams as $exam)
            @php $isActive = $exam->status === 'active'; @endphp
            <div class="col-md-6 col-lg-4 exam-card-daftar">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            @if($isActive)
                                <span class="badge bg-success rounded-pill d-inline-flex align-items-center gap-1"><i class="bi bi-play-circle-fill" style="font-size: 1.25em;"></i> Berjalan</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill d-inline-flex align-items-center gap-1"><i class="bi bi-pause-circle-fill" style="font-size: 1.25em;"></i> Belum Dimulai</span>
                            @endif
                            <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                        </div>
                        <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                        <p class="text-muted small mb-2">{{ Str::limit($exam->deskripsi, 80) }}</p>
                        <div class="d-flex gap-2 text-muted small mb-4">
                            <span><i class="bi bi-file-text me-1"></i>{{ $exam->soals_count }} Soal</span>
                            <span><i class="bi bi-check2-square me-1"></i>PG: {{ $exam->passing_grade }} Pts</span>
                        </div>
                        <a href="{{ route('pengawas.ujian.detail', $exam->ujian_id) }}"
                           class="btn {{ $isActive ? 'btn-unsulbar' : 'btn-outline-unsulbar' }} w-100 fw-semibold mt-auto">
                            Monitoring & Manajemen
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                    <p>Belum ada ujian yang dibuat. Silakan minta Admin untuk membuat ujian terlebih dahulu.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Ujian Selesai --}}
    @if($finishedExams->count() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-check-circle-fill text-secondary me-2"></i>Ujian Selesai</h4>
            <div id="pagination-ujian-selesai"></div>
        </div>
        <div class="row g-4 mb-5" id="container-ujian-selesai">
            @foreach($finishedExams as $exam)
                <div class="col-md-6 col-lg-4 exam-card-selesai">
                    <div class="card h-100 border-0 shadow-sm rounded-4 opacity-75">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-secondary rounded-pill d-inline-flex align-items-center gap-1"><i class="bi bi-check-circle-fill" style="font-size: 1.25em;"></i> Selesai</span>
                                <span class="text-muted small"><i class="bi bi-clock"></i> {{ $exam->durasi }} Menit</span>
                            </div>
                            <h5 class="fw-bold mb-2">{{ $exam->judul }}</h5>
                            <p class="text-muted small mb-2">{{ Str::limit($exam->deskripsi, 80) }}</p>
                            <div class="d-flex gap-2 text-muted small mb-4">
                                <span><i class="bi bi-file-text me-1"></i>{{ $exam->soals_count }} Soal</span>
                                <span><i class="bi bi-check2-square me-1"></i>PG: {{ $exam->passing_grade }} Pts</span>
                            </div>
                            <a href="{{ route('pengawas.ujian.detail', $exam->ujian_id) }}"
                               class="btn btn-outline-secondary w-100 fw-semibold mt-auto">
                                Lihat Hasil
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initCardPagination(containerId, cardClass, paginationContainerId, itemsPerPage = 3) {
        const container = document.getElementById(containerId);
        const paginationContainer = document.getElementById(paginationContainerId);
        if (!container || !paginationContainer) return;

        const cards = container.querySelectorAll('.' + cardClass);
        if (cards.length === 0 || container.querySelector('.bi-inbox')) {
            paginationContainer.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(cards.length / itemsPerPage);
        let currentPage = 1;

        function render() {
            cards.forEach((card, index) => {
                const start = (currentPage - 1) * itemsPerPage;
                const end = start + itemsPerPage;
                if (index >= start && index < end) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            if (totalPages <= 1) {
                paginationContainer.innerHTML = '';
                return;
            }

            let html = `<nav><ul class="pagination pagination-sm mb-0 shadow-sm">`;
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link px-3" href="#" data-page="${currentPage - 1}">&laquo;</a>
                     </li>`;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }
            for (let i = startPage; i <= endPage; i++) {
                html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                            <a class="page-link px-3" href="#" data-page="${i}">${i}</a>
                         </li>`;
            }
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link px-3" href="#" data-page="${currentPage + 1}">&raquo;</a>
                     </li>`;
            html += `</ul></nav>`;

            paginationContainer.innerHTML = html;

            paginationContainer.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.getAttribute('data-page'));
                    if (!isNaN(page) && page >= 1 && page <= totalPages) {
                        currentPage = page;
                        render();
                    }
                });
            });
        }

        render();
    }

    initCardPagination('container-daftar-ujian', 'exam-card-daftar', 'pagination-daftar-ujian', 3);
    initCardPagination('container-ujian-selesai', 'exam-card-selesai', 'pagination-ujian-selesai', 3);
});
</script>
@endsection
