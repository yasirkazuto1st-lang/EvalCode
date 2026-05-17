@extends('layouts.sidebar')

@section('title', 'Riwayat Submisi Peserta')

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
    <a href="{{ route('admin.ujian.detail', $exam->ujian_id) }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Riwayat Submisi: {{ $user->name }}</h4>
                <p class="mb-0 opacity-75 fs-6">{{ $user->nim_username }} | Ujian: {{ $exam->judul }}</p>
            </div>
            <i class="bi bi-journal-code opacity-25" style="font-size: 3rem;"></i>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-primary me-2"></i> Detail Submisi (Terbaru - Terlama)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-nowrap border-bottom border-primary border-2">
                            <th class="ps-4 py-3">Waktu</th>
                            <th class="py-3">Soal</th>
                            <th class="py-3">Status Judge0</th>
                            <th class="py-3">Skor</th>
                            <th class="py-3">Similarity (Plagiarisme)</th>
                            <th class="py-3">Source Code</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($submissions as $s)
                            <tr>
                                <td class="ps-4 text-nowrap">{{ \Carbon\Carbon::parse($s->created_at)->format('d M Y, H:i:s') }}</td>
                                <td class="fw-semibold">{{ $s->nama_soal }}</td>
                                <td>
                                    @if($s->status == 'Accepted')
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
                                <td><strong>{{ $s->skor }}</strong> / {{ $s->bobot_nilai }}</td>
                                <td>
                                    @if($s->similarity_index !== null)
                                        @php
                                            $threshold = config('judge.plagiarism_threshold', 0.75) * 100;
                                            $warnThreshold = $threshold / 2;
                                            $simColor = $s->similarity_index >= $threshold ? 'danger' : ($s->similarity_index >= $warnThreshold ? 'warning text-dark' : 'success');
                                        @endphp
                                        <span class="badge bg-{{ $simColor }}">{{ round($s->similarity_index, 2) }}%</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#codeModal{{ $s->submission_id }}">
                                        <i class="bi bi-code-slash me-1"></i> Lihat Kode
                                    </button>

                                    <!-- Code Modal -->
                                    <div class="modal fade" id="codeModal{{ $s->submission_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-code text-primary me-2"></i>Source Code</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre class="bg-dark text-light p-3 rounded mt-2" style="max-height: 400px; overflow-y: auto;"><code>{{ $s->source_code }}</code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada riwayat submisi.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
