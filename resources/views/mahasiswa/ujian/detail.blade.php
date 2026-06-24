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
            <i class="bi bi-mortarboard position-absolute opacity-10 card-bg-illustration"
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
                <div class="text-md-end mt-3 mt-md-0 d-flex flex-wrap gap-3 justify-content-md-end">
                    <!-- Box 1: Progress Anda -->
                    <div class="bg-black bg-opacity-25 rounded-4 p-3 border border-white border-opacity-25 shadow-sm text-center flex-grow-1 flex-md-grow-0"
                        style="backdrop-filter: blur(10px); min-width: 140px;">
                        <span class="small opacity-75 d-block text-uppercase fw-semibold mb-1"
                            style="letter-spacing: 1px; font-size: 10px;">Progress Anda</span>
                        <h4 class="fw-bold mb-0 text-white">{{ $acceptedCount }} / {{ $exam->soals->count() }} <span
                                class="fs-6 fw-normal">Soal</span></h4>
                    </div>

                    <!-- Box 2: Status Kelulusan -->
                    @php
                        $isPassed = $myTotalScore >= $exam->passing_grade;
                        $statusBg = $isPassed
                            ? 'bg-success bg-opacity-25 border-success'
                            : 'bg-danger bg-opacity-25 border-danger';
                        $statusText = $isPassed ? 'LULUS' : 'TIDAK LULUS';
                        $statusIcon = $isPassed ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger';
                    @endphp
                    <div class="rounded-4 p-3 border border-opacity-50 shadow-sm text-center flex-grow-1 flex-md-grow-0 {{ $statusBg }}"
                        style="backdrop-filter: blur(10px); min-width: 150px;">
                        <span class="small opacity-75 d-block text-uppercase fw-semibold mb-1 text-white"
                            style="letter-spacing: 1px; font-size: 10px;">Status Kelulusan</span>
                        <h4 class="fw-bold mb-0 text-white d-flex align-items-center justify-content-center gap-2">
                            <i class="bi {{ $statusIcon }} fs-5"></i>
                            <span>{{ $statusText }}</span>
                        </h4>
                    </div>

                    <!-- Box 3: Sisa Waktu Ujian -->
                    <div class="bg-black bg-opacity-25 rounded-4 p-3 border border-white border-opacity-25 shadow-sm text-center flex-grow-1 flex-md-grow-0"
                        style="backdrop-filter: blur(10px); min-width: 150px;">
                        <span class="small opacity-75 d-block text-uppercase fw-semibold mb-1 text-white"
                            style="letter-spacing: 1px; font-size: 10px;">Sisa Waktu Ujian</span>
                        <h4 id="studentTimerDisplay" class="fw-bold mb-0 text-white"
                            style="font-family: 'Courier New', monospace; letter-spacing: 1px; font-size: 1.5rem;">--:--:--
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 Kolom Konten -->
        <div class="row g-4">
            <!-- Kiri: Leaderboard -->
            <div class="col-xl-4 d-none d-xl-block">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="fw-bold mb-0"><i class="bi bi-trophy text-warning me-2"></i> Leaderboard</h5>
                    </div>
                    <div class="card-body p-3 mt-2 position-relative d-flex flex-column">
                        <!-- Scrollable Leaderboard List (Max 5 items view) -->
                        <div id="leaderboardScrollContainer" class="leaderboard-scroll pe-2 mb-1"
                            style="max-height: 340px; overflow-y: auto;">
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

                                        $isMe = $lb->user_id == Auth::id();
                                        if ($isMe) {
                                            $borderClass = 'border border-2 border-primary';
                                            if ($idx > 2) {
                                                $bgClass = 'bg-primary bg-opacity-10';
                                                $textClass = 'text-primary';
                                                $badgeClass = 'bg-primary text-white';
                                            }
                                        }
                                    @endphp
                                    <li @if ($isMe) id="myLeaderboardListItem" @endif
                                        class="list-group-item d-flex justify-content-between align-items-center py-3 {{ $borderClass }} {{ $bgClass }} rounded mb-2 shadow-sm">
                                        <div class="d-flex align-items-center me-3" style="min-width: 0;">
                                            <span
                                                class="badge {{ $badgeClass }} rounded-pill me-2 px-2 py-1 fs-6 flex-shrink-0">
                                                {!! $icon !!}{{ $idx + 1 }}
                                            </span>
                                            <span
                                                class="fw-semibold me-1 text-nowrap text-truncate">{{ $lb->name }}</span>
                                            @if ($isMe)
                                                <span class="badge bg-primary ms-1 small flex-shrink-0">Anda</span>
                                            @endif
                                        </div>
                                        <span class="fw-bold {{ $textClass }} flex-shrink-0">{{ $lb->total_skor }}
                                            Pts</span>
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
                            foreach ($leaderboard as $idx => $lb) {
                                if ($lb->user_id == Auth::id()) {
                                    $myRankIndex = $idx;
                                    $myLeaderboardItem = $lb;
                                    break;
                                }
                            }
                        @endphp

                        @if ($myLeaderboardItem)
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
                            <div id="floatingMyRankBar" class="position-absolute"
                                style="left: 1rem; right: 1.5rem; bottom: 1rem; z-index: 10; transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0; transform: translateY(15px); pointer-events: none;"
                                title="Klik untuk melihat posisi Anda">
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 {{ $myBorderClass }} {{ $myBgClass }} rounded mb-0 shadow-lg"
                                    style="cursor: pointer; backdrop-filter: blur(8px); background-color: rgba(255,255,255,0.92);">
                                    <div class="d-flex align-items-center me-3" style="min-width: 0;">
                                        <span
                                            class="badge {{ $myBadgeClass }} rounded-pill me-2 px-2 py-1 fs-6 flex-shrink-0">
                                            {!! $myIcon !!}{{ $myRankIndex + 1 }}
                                        </span>
                                        <span
                                            class="fw-semibold me-1 text-nowrap text-truncate">{{ $myLeaderboardItem->name }}</span>
                                        <span class="badge bg-primary ms-1 small flex-shrink-0">Anda</span>
                                    </div>
                                    <span
                                        class="fw-bold {{ $myTextClass }} flex-shrink-0">{{ $myLeaderboardItem->total_skor }}
                                        Pts</span>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const listItem = document.getElementById('myLeaderboardListItem');
                                    const floatingBar = document.getElementById('floatingMyRankBar');
                                    const scrollContainer = document.getElementById('leaderboardScrollContainer');

                                    if (listItem && floatingBar && scrollContainer) {
                                        const checkVisibility = () => {
                                            const containerRect = scrollContainer.getBoundingClientRect();
                                            const itemRect = listItem.getBoundingClientRect();

                                            // Toleransi 5px
                                            const isVisible = (itemRect.top >= containerRect.top - 5) && (itemRect.bottom <=
                                                containerRect.bottom + 5);

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
                                            listItem.scrollIntoView({
                                                behavior: 'smooth',
                                                block: 'center'
                                            });
                                        });
                                    }
                                });
                            </script>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Kanan: Daftar Soal -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-list-task text-primary me-2"></i> Daftar Soal</h5>
                        <button class="btn btn-sm btn-outline-warning rounded-pill px-3 d-xl-none fw-bold" type="button"
                            data-bs-toggle="offcanvas" data-bs-target="#leaderboardOffcanvas"
                            aria-controls="leaderboardOffcanvas">
                            <i class="bi bi-trophy-fill me-1 text-warning"></i> Leaderboard
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($exam->soals as $index => $soal)
                                <div class="col-12">
                                    @php
                                        $cardClass = 'soal-card-default';
                                        if ($soal->status_pengerjaan == 'Accepted') {
                                            $cardClass = 'soal-card-accepted border-2';
                                        } elseif ($soal->status_pengerjaan == 'Wrong Answer') {
                                            $cardClass = 'soal-card-wrong border-2';
                                        } elseif ($soal->status_pengerjaan == 'Time Limit Exceeded') {
                                            $cardClass = 'soal-card-tle border-2';
                                        } elseif ($soal->status_pengerjaan == 'Compilation Error') {
                                            $cardClass = 'soal-card-ce border-2';
                                        } elseif ($soal->status_pengerjaan == 'Runtime Error') {
                                            $cardClass = 'soal-card-re border-2';
                                        }
                                    @endphp
                                    <div
                                        class="border rounded-4 p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center shadow-sm hover-shadow transition-all {{ $cardClass }}">
                                        <div class="mb-3 mb-md-0">
                                            <h6 class="fw-bold mb-1 text-dark">{{ $index + 1 }}. {{ $soal->nama_soal }}
                                            </h6>
                                            <div class="text-muted small mt-2 d-flex gap-3 flex-wrap">
                                                <span><i class="bi bi-star-fill text-warning me-1"></i>Bobot:
                                                    {{ $soal->bobot_nilai }}</span>

                                                <span><i class="bi bi-send me-1"></i>Submit: {{ $soal->attempts_used }} /
                                                    {{ $exam->max_attempt }}</span>

                                                @php
                                                    $statusStyle = 'color: #6c757d;';
                                                    if ($soal->status_pengerjaan == 'Accepted') {
                                                        $statusStyle = 'color: #198754;';
                                                    } elseif ($soal->status_pengerjaan == 'Wrong Answer') {
                                                        $statusStyle = 'color: #dc3545;';
                                                    } elseif ($soal->status_pengerjaan == 'Time Limit Exceeded') {
                                                        $statusStyle = 'color: #d97706;';
                                                    } elseif ($soal->status_pengerjaan == 'Compilation Error') {
                                                        $statusStyle = 'color: #6c757d;';
                                                    } elseif ($soal->status_pengerjaan == 'Runtime Error') {
                                                        $statusStyle = 'color: #9333ea;';
                                                    }
                                                @endphp

                                                <span class="fw-semibold" style="{{ $statusStyle }}">
                                                    Status: {{ $soal->status_pengerjaan }}
                                                    @if ($soal->status_pengerjaan != 'Belum Dikerjakan')
                                                        (Skor Tertinggi: {{ $soal->skor_tertinggi }})
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        @if ($soal->status_pengerjaan == 'Accepted')
                                            <a href="{{ route('workspace', ['examId' => $exam->ujian_id, 'soalId' => $soal->soal_id]) }}"
                                                class="btn btn-sm btn-outline-success rounded-pill px-4 shadow-sm">
                                                <i class="bi bi-check-circle-fill me-1"></i> Selesai
                                            </a>
                                        @elseif ($soal->attempts_used >= $exam->max_attempt)
                                            <button class="btn btn-sm btn-secondary rounded-pill px-4 shadow-sm" disabled
                                                style="cursor: not-allowed;">
                                                <i class="bi bi-x-circle me-1"></i> Batas Submit
                                                ({{ $exam->max_attempt }}/{{ $exam->max_attempt }})
                                            </button>
                                        @else
                                            <a href="{{ route('workspace', ['examId' => $exam->ujian_id, 'soalId' => $soal->soal_id]) }}"
                                                class="btn btn-sm btn-primary rounded-pill px-4 shadow-sm">Mulai
                                                Kerjakan</a>
                                        @endif
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
        .soal-card-accepted {
            background-color: rgba(25, 135, 84, 0.1);
            border-color: #198754 !important;
        }

        .soal-card-wrong {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: #dc3545 !important;
        }

        .soal-card-tle {
            background-color: rgba(255, 193, 7, 0.1);
            border-color: #ffc107 !important;
        }

        .soal-card-ce {
            background-color: rgba(108, 117, 125, 0.1);
            border-color: #6c757d !important;
        }

        .soal-card-re {
            background-color: rgba(168, 85, 247, 0.08);
            border-color: rgba(168, 85, 247, 0.4) !important;
        }

        .soal-card-default {
            background-color: #ffffff;
            border-color: var(--bs-border-color-translucent) !important;
        }

        div.hover-shadow.soal-card-accepted:hover,
        div.hover-shadow.soal-card-wrong:hover,
        div.hover-shadow.soal-card-tle:hover,
        div.hover-shadow.soal-card-ce:hover,
        div.hover-shadow.soal-card-re:hover,
        div.hover-shadow.soal-card-default:hover {
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

    <script>
        (function() {
            let examRemaining = {{ $exam->getRemainingSeconds() }};
            const studentTimerDisplay = document.getElementById('studentTimerDisplay');
            let studentTimer = null;
            let statusPoll = null;
            let isRedirecting = false;

            function handleRedirect(message) {
                if (isRedirecting) return;
                isRedirecting = true;
                if (studentTimer) clearInterval(studentTimer);
                if (statusPoll) clearInterval(statusPoll);
                alert(message);
                window.location.href = '/dashboard';
            }

            function formatTime(seconds) {
                const totalSeconds = Math.floor(seconds);
                const h = Math.floor(totalSeconds / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;
                return [
                    h.toString().padStart(2, '0'),
                    m.toString().padStart(2, '0'),
                    s.toString().padStart(2, '0')
                ].join(':');
            }

            function updateStudentTimer() {
                if (examRemaining <= 0) {
                    if (studentTimerDisplay) studentTimerDisplay.textContent = '00:00:00';
                    handleRedirect("Waktu ujian telah habis! Anda akan dialihkan ke dashboard.");
                    return;
                }
                if (studentTimerDisplay) {
                    studentTimerDisplay.textContent = formatTime(examRemaining);
                }
                examRemaining--;
            }

            async function fetchWithRetry(url, options = {}, retries = 3, delay = 1000) {
                try {
                    const response = await fetch(url, options);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response;
                } catch (error) {
                    if (retries > 0) {
                        console.warn(`Fetch failed, retrying in ${delay}ms... (${retries} retries left)`, error);
                        await new Promise(resolve => setTimeout(resolve, delay));
                        return fetchWithRetry(url, options, retries - 1, delay * 2);
                    }
                    throw error;
                }
            }

            let connWarningDiv = null;

            function showConnectionWarning(show) {
                if (show) {
                    if (!connWarningDiv) {
                        connWarningDiv = document.createElement('div');
                        connWarningDiv.id = 'connection-warning-alert';
                        connWarningDiv.className =
                            'alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3 z-3 rounded-4 shadow-sm';
                        connWarningDiv.style.zIndex = '9999';
                        connWarningDiv.innerHTML =
                            '<i class="bi bi-wifi-off me-2"></i><strong>Koneksi Bermasalah.</strong> Menghubungkan kembali ke server...';
                        document.body.appendChild(connWarningDiv);
                    }
                } else {
                    if (connWarningDiv) {
                        connWarningDiv.remove();
                        connWarningDiv = null;
                    }
                }
            }

            if (studentTimerDisplay) {
                updateStudentTimer();
                studentTimer = setInterval(updateStudentTimer, 1000);
            }

            // Poll for exam status every 15 seconds (reduced from 5s to lower server load)
            let isPollingStatus = false;
            statusPoll = setInterval(async () => {
                if (isPollingStatus) return;
                isPollingStatus = true;
                try {
                    const response = await fetchWithRetry(
                        "{{ route('ujian.status', $exam->ujian_id) }}", {}, 2, 1000);
                    const data = await response.json();
                    showConnectionWarning(false); // Hide warning if request succeeds
                    if (data.status !== 'active') {
                        handleRedirect("Ujian telah ditutup atau di-pause oleh pengawas!");
                        return;
                    }
                    // Sync remaining time
                    examRemaining = data.remainingSeconds;
                } catch (error) {
                    console.error("Gagal memeriksa status ujian:", error);
                    showConnectionWarning(true); // Show warning when connection drops
                } finally {
                    isPollingStatus = false;
                }
            }, 15000);

            window.addEventListener('beforeunload', () => {
                if (studentTimer) clearInterval(studentTimer);
                if (statusPoll) clearInterval(statusPoll);
            });
        })();
    </script>

    <!-- Offcanvas Leaderboard for Screen sizes < 1200px (Laptop S and down) -->
    <div class="offcanvas offcanvas-start d-xl-none leaderboard-offcanvas-drawer" tabindex="-1"
        id="leaderboardOffcanvas" aria-labelledby="leaderboardOffcanvasLabel">
        <div class="offcanvas-header border-bottom bg-white">
            <h5 class="offcanvas-title fw-bold" id="leaderboardOffcanvasLabel">
                <i class="bi bi-trophy text-warning me-2"></i> Leaderboard
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3 bg-light">
            <div class="leaderboard-scroll pe-1" style="max-height: calc(100vh - 100px); overflow-y: auto;">
                <ul class="list-group list-group-flush pb-2">
                    @forelse($leaderboard as $idx => $lb)
                        @php
                            $bgClass = 'bg-white';
                            $textClass = 'text-secondary';
                            $badgeClass = 'bg-dark bg-opacity-25 text-dark';
                            $icon = '';
                            $borderClass = 'border-0';

                            if ($idx == 0) {
                                $bgClass = 'bg-warning bg-opacity-10';
                                $textClass = 'text-warning';
                                $badgeClass = 'bg-warning text-dark shadow-sm';
                                $icon = '<i class="bi bi-trophy-fill me-1"></i>';
                                $borderClass = 'border border-warning border-opacity-50';
                            } elseif ($idx == 1) {
                                $bgClass = 'bg-secondary bg-opacity-10';
                                $textClass = 'text-secondary';
                                $badgeClass = 'bg-secondary text-white shadow-sm';
                                $icon = '<i class="bi bi-award-fill me-1"></i>';
                                $borderClass = 'border border-secondary border-opacity-50';
                            } elseif ($idx == 2) {
                                $bgClass = 'bg-danger bg-opacity-10';
                                $textClass = 'text-danger';
                                $badgeClass = 'bg-danger text-white shadow-sm';
                                $icon = '<i class="bi bi-award-fill me-1"></i>';
                                $borderClass = 'border border-danger border-opacity-50';
                            }

                            $isMe = $lb->user_id == Auth::id();
                            if ($isMe) {
                                $borderClass = 'border border-2 border-primary';
                                if ($idx > 2) {
                                    $bgClass = 'bg-primary bg-opacity-10';
                                    $textClass = 'text-primary';
                                    $badgeClass = 'bg-primary text-white';
                                }
                            }
                        @endphp
                        <li
                            class="list-group-item d-flex justify-content-between align-items-center py-3 {{ $borderClass }} {{ $bgClass }} rounded mb-2 shadow-sm">
                            <div class="d-flex align-items-center me-3" style="min-width: 0;">
                                <span class="badge {{ $badgeClass }} rounded-pill me-2 px-2 py-1 fs-6 flex-shrink-0">
                                    {!! $icon !!}{{ $idx + 1 }}
                                </span>
                                <span class="fw-semibold me-1 text-nowrap text-truncate">{{ $lb->name }}</span>
                                @if ($isMe)
                                    <span class="badge bg-primary ms-1 small flex-shrink-0">Anda</span>
                                @endif
                            </div>
                            <span class="fw-bold {{ $textClass }} flex-shrink-0">{{ $lb->total_skor }} Pts</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted py-4 border-0">
                            Belum ada data leaderboard.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
