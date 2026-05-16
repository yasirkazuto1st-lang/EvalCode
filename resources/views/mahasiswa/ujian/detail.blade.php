@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Back Button -->
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-back mb-3">
            <i class="bi bi-arrow-left"></i>
        </a>

        <!-- Header Informasi Ujian -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 detail-ujian-header position-relative overflow-hidden"
            style="background: linear-gradient(135deg, var(--bs-primary) 0%, #4a0000 100%); color: white;">
            <i class="bi bi-mortarboard position-absolute opacity-10"
                style="font-size: 12rem; right: -2rem; top: -3rem; transform: rotate(15deg);"></i>

            <div
                class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center position-relative z-1">
                <div class="mb-3 mb-md-0">
                    <span class="badge bg-white text-primary mb-2 px-2 py-1 rounded-pill shadow-sm fw-bold"><i
                            class="bi bi-circle-fill text-success small me-1"></i> Sedang Berlangsung</span>
                    <h4 class="fw-bold mb-1">{{ $exam->judul }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-3 opacity-75 fs-6 mt-1">
                        <span><i class="bi bi-clock me-1"></i> Waktu: {{ $exam->durasi }} Menit</span>
                        <span><i class="bi bi-check2-square me-1"></i> Passing Grade: {{ $exam->passing_grade }} Pts</span>
                    </div>
                </div>
                <div class="text-md-end mt-3 mt-md-0">
                    <div class="bg-black bg-opacity-25 rounded-4 p-3 border border-white border-opacity-25 shadow-sm text-center"
                        style="backdrop-filter: blur(10px); min-width: 150px;">
                        <span class="small opacity-75 d-block text-uppercase fw-semibold"
                            style="letter-spacing: 1px; font-size: 10px;">Progress Anda</span>
                        <!-- Placeholder progress, can be dynamic later -->
                        <h4 class="fw-bold mb-0 text-white">{{ $acceptedCount }} / {{ $exam->soals->count() }} <span
                                class="fs-6 fw-normal">Soal</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 Kolom Konten -->
        <div class="row g-4">
            <!-- Kiri: Leaderboard -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i> Leaderboard</h5>
                    </div>
                    <div class="card-body p-3 mt-2 position-relative d-flex flex-column">
                        <!-- Scrollable Leaderboard List (Max 5 items view) -->
                        <div id="leaderboardScrollContainer" class="leaderboard-scroll pe-2 mb-1" style="max-height: 340px; overflow-y: auto;">
                            <ul class="list-group list-group-flush pb-2">
                                @forelse($leaderboard as $idx => $lb)
                                    @php
                                        $bgClass = 'bg-light';
                                        $textClass = 'text-secondary';
                                        $badgeClass = 'bg-dark bg-opacity-25 text-dark';
                                        $icon = '';
                                        $borderClass = 'border-0';

                                        // Juara 1: Emas (Gold)
                                        if ($idx == 0) {
                                            $bgClass = 'bg-warning bg-opacity-10';
                                            $textClass = 'text-warning';
                                            $badgeClass = 'bg-warning text-dark shadow-sm';
                                            $icon = '<i class="bi bi-trophy-fill me-1"></i>';
                                            $borderClass = 'border border-warning border-opacity-50';
                                        } 
                                        // Juara 2: Perak (Silver)
                                        elseif ($idx == 1) {
                                            $bgClass = 'bg-secondary bg-opacity-10';
                                            $textClass = 'text-secondary';
                                            $badgeClass = 'bg-secondary text-white shadow-sm';
                                            $icon = '<i class="bi bi-award-fill me-1"></i>';
                                            $borderClass = 'border border-secondary border-opacity-50';
                                        } 
                                        // Juara 3: Perunggu (Bronze)
                                        elseif ($idx == 2) {
                                            $bgClass = 'bg-danger bg-opacity-10';
                                            $textClass = 'text-danger';
                                            $badgeClass = 'bg-danger text-white shadow-sm';
                                            $icon = '<i class="bi bi-award-fill me-1"></i>';
                                            $borderClass = 'border border-danger border-opacity-50';
                                        }

                                        $isMe = ($lb->user_id == Auth::id());
                                        if ($isMe) {
                                            $borderClass = 'border border-2 border-primary';
                                            if ($idx > 2) {
                                                $bgClass = 'bg-primary bg-opacity-10';
                                                $textClass = 'text-primary';
                                                $badgeClass = 'bg-primary text-white';
                                            }
                                        }
                                    @endphp
                                    <li @if($isMe) id="myLeaderboardListItem" @endif class="list-group-item d-flex justify-content-between align-items-center py-3 {{ $borderClass }} {{ $bgClass }} rounded mb-2 shadow-sm">
                                        <div class="d-flex align-items-center">
                                            <span class="badge {{ $badgeClass }} rounded-pill me-2 px-2 py-1 fs-6">
                                                {!! $icon !!}{{ $idx + 1 }}
                                            </span>
                                            <span class="fw-semibold me-1">{{ $lb->name }}</span>
                                            @if ($isMe)
                                                <span class="badge bg-primary ms-1 small">Anda</span>
                                            @endif
                                        </div>
                                        <span class="fw-bold {{ $textClass }}">{{ $lb->total_skor }} Pts</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-4 border-0">
                                        Belum ada data leaderboard.
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Floating Pinned Bottom Section: Indikator "Anda" (Muncul jika item asli di luar viewport) -->
                        @php
                            $myRankIndex = null;
                            $myLeaderboardItem = null;
                            foreach($leaderboard as $idx => $lb) {
                                if ($lb->user_id == Auth::id()) {
                                    $myRankIndex = $idx;
                                    $myLeaderboardItem = $lb;
                                    break;
                                }
                            }
                        @endphp

                        @if($myLeaderboardItem)
                            @php
                                $myBgClass = 'bg-light';
                                $myTextClass = 'text-secondary';
                                $myBadgeClass = 'bg-dark bg-opacity-25 text-dark';
                                $myIcon = '';
                                $myBorderClass = 'border-0';

                                if ($myRankIndex == 0) {
                                    $myBgClass = 'bg-warning bg-opacity-10';
                                    $myTextClass = 'text-warning';
                                    $myBadgeClass = 'bg-warning text-dark shadow-sm';
                                    $myIcon = '<i class="bi bi-trophy-fill me-1"></i>';
                                    $myBorderClass = 'border border-warning border-opacity-50';
                                } elseif ($myRankIndex == 1) {
                                    $myBgClass = 'bg-secondary bg-opacity-10';
                                    $myTextClass = 'text-secondary';
                                    $myBadgeClass = 'bg-secondary text-white shadow-sm';
                                    $myIcon = '<i class="bi bi-award-fill me-1"></i>';
                                    $myBorderClass = 'border border-secondary border-opacity-50';
                                } elseif ($myRankIndex == 2) {
                                    $myBgClass = 'bg-danger bg-opacity-10';
                                    $myTextClass = 'text-danger';
                                    $myBadgeClass = 'bg-danger text-white shadow-sm';
                                    $myIcon = '<i class="bi bi-award-fill me-1"></i>';
                                    $myBorderClass = 'border border-danger border-opacity-50';
                                }

                                // Tetap berikan border tebal primary karena ini kita
                                $myBorderClass = 'border border-2 border-primary';
                                if ($myRankIndex > 2) {
                                    $myBgClass = 'bg-primary bg-opacity-10';
                                    $myTextClass = 'text-primary';
                                    $myBadgeClass = 'bg-primary text-white';
                                }
                            @endphp
                            <div id="floatingMyRankBar" class="position-absolute" style="left: 1rem; right: 1.5rem; bottom: 1rem; z-index: 10; transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; transform: translateY(15px); pointer-events: none;" title="Klik untuk melihat posisi Anda">
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 {{ $myBorderClass }} {{ $myBgClass }} rounded mb-0 shadow-lg" style="cursor: pointer; backdrop-filter: blur(8px); background-color: rgba(255,255,255,0.92);">
                                    <div class="d-flex align-items-center">
                                        <span class="badge {{ $myBadgeClass }} rounded-pill me-2 px-2 py-1 fs-6">
                                            {!! $myIcon !!}{{ $myRankIndex + 1 }}
                                        </span>
                                        <span class="fw-semibold me-1">{{ $myLeaderboardItem->name }}</span>
                                        <span class="badge bg-primary ms-1 small">Anda</span>
                                    </div>
                                    <span class="fw-bold {{ $myTextClass }}">{{ $myLeaderboardItem->total_skor }} Pts</span>
                                </div>
                            </div>

                            <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const listItem = document.getElementById('myLeaderboardListItem');
                                const floatingBar = document.getElementById('floatingMyRankBar');
                                const scrollContainer = document.getElementById('leaderboardScrollContainer');

                                if (listItem && floatingBar && scrollContainer) {
                                    const checkVisibility = () => {
                                        const containerRect = scrollContainer.getBoundingClientRect();
                                        const itemRect = listItem.getBoundingClientRect();

                                        // Toleransi 5px
                                        const isVisible = (itemRect.top >= containerRect.top - 5) && (itemRect.bottom <= containerRect.bottom + 5);

                                        if (isVisible) {
                                            floatingBar.style.opacity = '0';
                                            floatingBar.style.transform = 'translateY(15px)';
                                            floatingBar.style.pointerEvents = 'none';
                                        } else {
                                            floatingBar.style.opacity = '1';
                                            floatingBar.style.transform = 'translateY(0)';
                                            floatingBar.style.pointerEvents = 'auto';
                                        }
                                    };

                                    scrollContainer.addEventListener('scroll', checkVisibility);
                                    window.addEventListener('resize', checkVisibility);
                                    setTimeout(checkVisibility, 150);

                                    floatingBar.addEventListener('click', function() {
                                        listItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    });
                                }
                            });
                            </script>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kanan: Daftar Soal -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($exam->soals as $index => $soal)
                                <div class="col-12">
                                    @php
                                        $borderColor = 'border-light-subtle bg-white';
                                        if ($soal->status_pengerjaan == 'Accepted') {
                                            $borderColor = 'border-success border-2 bg-success bg-opacity-10';
                                        } elseif (
                                            in_array($soal->status_pengerjaan, [
                                                'Wrong Answer',
                                                'Time Limit Exceeded',
                                                'Runtime Error',
                                            ])
                                        ) {
                                            $borderColor = 'border-danger border-2 bg-danger bg-opacity-10';
                                        }
                                    @endphp
                                    <div
                                        class="border rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center shadow-sm hover-shadow transition-all {{ $borderColor }}">
                                        <div class="mb-3 mb-md-0">
                                            <h6 class="fw-bold mb-1 text-dark">{{ $index + 1 }}. {{ $soal->nama_soal }}
                                            </h6>
                                            <div class="text-muted small mt-2 d-flex gap-3 flex-wrap">
                                                <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot:
                                                    {{ $soal->bobot_nilai }}</span>

                                                @php
                                                    $statusColor = 'text-secondary';
                                                    if ($soal->status_pengerjaan == 'Accepted') {
                                                        $statusColor = 'text-success';
                                                    } elseif (
                                                        in_array($soal->status_pengerjaan, [
                                                            'Wrong Answer',
                                                            'Time Limit Exceeded',
                                                            'Runtime Error',
                                                        ])
                                                    ) {
                                                        $statusColor = 'text-danger';
                                                    }
                                                @endphp

                                                <span class="{{ $statusColor }} fw-semibold">
                                                    Status: {{ $soal->status_pengerjaan }}
                                                    @if ($soal->status_pengerjaan != 'Belum Dikerjakan')
                                                        (Skor Tertinggi: {{ $soal->skor_tertinggi }})
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('workspace', ['examId' => $exam->ujian_id, 'soalId' => $soal->soal_id]) }}"
                                            class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">Mulai Kerjakan</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    <p class="mb-0">Belum ada soal untuk ujian ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            border-color: #800000 !important;
        }

        .transition-all {
            transition: all .2s ease;
        }

        .btn-outline-unsulbar {
            color: #800000;
            border-color: #800000;
        }

        .btn-outline-unsulbar:hover {
            color: #fff;
            background-color: #800000;
            border-color: #800000;
        }
    </style>
@endsection
