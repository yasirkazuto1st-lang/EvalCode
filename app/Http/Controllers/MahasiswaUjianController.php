<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Token;
use App\Services\PlagiarismService;

class MahasiswaUjianController extends Controller
{
    protected $plagiarismService;

    public function __construct(PlagiarismService $plagiarismService)
    {
        $this->plagiarismService = $plagiarismService;
    }
    /**
     * Tampilkan dashboard Mahasiswa dengan daftar ujian yang tersedia.
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $activeExams = \Illuminate\Support\Facades\Cache::remember('active_exams', 10, function () {
            $list = Ujian::where('status', 'active')->orderBy('updated_at', 'desc')->get();
            foreach ($list as $exam) {
                $exam->checkTimeout();
            }
            return Ujian::where('status', 'active')->orderBy('updated_at', 'desc')->get();
        });

        $closedExams = \Illuminate\Support\Facades\Cache::remember('closed_exams', 10, function () {
            return Ujian::where('status', 'closed')->orderBy('updated_at', 'desc')->get();
        });

        $finishedExams = \Illuminate\Support\Facades\Cache::remember('finished_exams', 10, function () {
            return Ujian::where('status', 'finished')->orderBy('updated_at', 'desc')->get();
        });

        return view('mahasiswa.dashboard', compact('activeExams', 'closedExams', 'finishedExams'));
    }

    /**
     * Tampilkan detail ujian yang akan diikuti Mahasiswa (termasuk leaderboard dan status pengerjaan soal).
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function detail($id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);
        $exam->checkTimeout();
        
        // Ensure exam is active
        if ($exam->status !== 'active') {
            return redirect()->route('dashboard')->with('error', 'Ujian belum dimulai atau sudah ditutup.');
        }

        // Leaderboard Calculation
        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.user_id', 'submissions.soal_id', \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'))
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $leaderboard = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select('users.name', 'users.user_id', \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'))
            ->groupBy('users.name', 'users.user_id')
            ->orderByDesc('total_skor')
            ->take(10)
            ->get();

        // Get current user's submissions for this exam to show status on each soal
        $userSubmissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->where('submissions.user_id', \Illuminate\Support\Facades\Auth::id())
            ->get();

        $acceptedCount = 0;
        $myTotalScore = 0;

        foreach ($exam->soals as $soal) {
            $soalSubmissions = $userSubmissions->where('soal_id', $soal->soal_id);
            $soal->attempts_used = $soalSubmissions->where('is_reset', false)->count();

            $bestSubmission = $soalSubmissions->sortByDesc('skor')->first();
            if ($bestSubmission) {
                $soal->status_pengerjaan = $bestSubmission->status;
                $soal->skor_tertinggi = $bestSubmission->skor;
                $myTotalScore += $bestSubmission->skor;
                if ($soal->status_pengerjaan == 'Accepted') {
                    $acceptedCount++;
                }
            } else {
                $soal->status_pengerjaan = 'Belum Dikerjakan';
                $soal->skor_tertinggi = 0;
            }
        }

        return view('mahasiswa.ujian.detail', compact('exam', 'leaderboard', 'acceptedCount', 'myTotalScore'));
    }

    /**
     * Proses mahasiswa untuk bergabung ke dalam ujian menggunakan token ujian.
     * 
     * @param Request $request
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function joinExam(Request $request, $id)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $exam = Ujian::findOrFail($id);
        $exam->checkTimeout();

        if ($exam->status !== 'active') {
            return back()->with('error', 'Ujian tidak aktif.');
        }

        // Verify token
        $activeToken = Token::where('ujian_id', $exam->ujian_id)
            ->where('status_aktif', true)
            ->latest()
            ->first();

        if (!$activeToken || $activeToken->kode_token !== strtoupper($request->token)) {
            return back()->with('error', 'Token ujian tidak valid atau sudah kadaluarsa!');
        }

        // Token matches! For now, redirect to detail view to select questions.
        // In a real scenario, we might create a participant record here if it doesn't exist.
        return redirect()->route('ujian.detail', $exam->ujian_id)->with('success', 'Berhasil masuk ke ujian!');
    }

    /**
     * Tampilkan ruang kerja (workspace) coding bagi Mahasiswa untuk mengerjakan soal tertentu.
     * 
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function workspace($examId, $soalId)
    {
        $exam = Ujian::with('soals')->findOrFail($examId);
        $exam->checkTimeout();
        
        if ($exam->status !== 'active') {
            return redirect()->route('dashboard')->with('error', 'Ujian tidak aktif.');
        }

        $soal = \App\Models\Soal::with('testCases')->where('ujian_id', $examId)->findOrFail($soalId);
        
        // Count active attempts
        $attemptsUsed = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('is_reset', false)
            ->count();

        $remainingSeconds = $exam->getRemainingSeconds();

        $maxAttempt = $exam->max_attempt;

        // Check if user already has an Accepted submission
        $hasAccepted = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereIn('status', ['Accepted', 'accepted'])
            ->exists();

        return view('mahasiswa.workspace', compact('exam', 'soal', 'attemptsUsed', 'remainingSeconds', 'maxAttempt', 'hasAccepted'));
    }

    /**
     * Normalisasi string output: hapus trailing whitespace per baris,
     * dan JANGAN sertakan newline di baris paling terakhir.
     */
    private function normalizeOutput(string $str): string
    {
        // Trim keseluruhan, lalu normalize line endings
        $str = str_replace("\r\n", "\n", $str);
        $str = str_replace("\r", "\n", $str);

        // Pecah per baris, rtrim tiap baris, gabung kembali
        $lines = explode("\n", $str);

        // Hapus baris kosong di akhir (trailing empty lines)
        while (count($lines) > 0 && trim(end($lines)) === '') {
            array_pop($lines);
        }

        // rtrim setiap baris (hapus trailing space/tab per baris)
        $lines = array_map('rtrim', $lines);

        // Gabung kembali TANPA trailing newline di baris terakhir
        return implode("\n", $lines);
    }

    /**
     * Resolve status Judge0 ke string status yang dikenali oleh frontend EvalCode.
     *
     * Mapping:
     *  - id 3       => "Accepted"
     *  - id 4       => "Wrong Answer"
     *  - id 5       => "Time Limit Exceeded"
     *  - id 6       => "Compilation Error"
     *  - id 7-12,14 => cek compile_output untuk upgrade ke "Compilation Error", default "Runtime Error"
     *  - lainnya    => "Runtime Error"
     *
     * @param  int    $statusId        Status ID dari Judge0
     * @param  string $statusDesc      Deskripsi status dari Judge0
     * @param  string $compileOutput   compile_output dari Judge0 (sudah di-decode)
     * @param  string $stderr          stderr dari Judge0 (sudah di-decode)
     * @return string
     */
    private function resolveJudge0StatusForEvalcode(
        int $statusId,
        string $statusDesc,
        string $compileOutput,
        string $stderr
    ): string {
        // === Perbaikan #2a: Status ID 6 selalu Compilation Error ===
        if ($statusId === 6) {
            return 'Compilation Error';
        }

        // === Perbaikan #2b: Deskripsi mengandung kata compile/syntax error ===
        $descLower = strtolower($statusDesc);
        if (
            str_contains($descLower, 'compilation error') ||
            str_contains($descLower, 'compile error') ||
            str_contains($descLower, 'syntax error')
        ) {
            return 'Compilation Error';
        }

        // Status Accepted / Wrong Answer / TLE tetap langsung
        if ($statusId === 3) {
            return 'Accepted';
        }
        if ($statusId === 4) {
            return 'Wrong Answer';
        }
        if ($statusId === 5) {
            return 'Time Limit Exceeded';
        }

        // === Perbaikan #2c: Runtime range (7-12, 14) — cek upgrade ke CE ===
        if (in_array($statusId, [7, 8, 9, 10, 11, 12, 14])) {
            $combinedError = strtolower($compileOutput . ' ' . $stderr);
            $compilerPatterns = [
                'error:',
                'javac',
                'gcc',
                'g++',
                'cannot find symbol',
                'syntaxerror',
                'syntax error',
                'indentationerror',
                'nameerror',
                'modulenotfounderror',
                'compileerror',
                'compilation error',
                'undefined reference',
                'fatal error',
            ];

            foreach ($compilerPatterns as $pattern) {
                if (str_contains($combinedError, $pattern)) {
                    return 'Compilation Error';
                }
            }

            return 'Runtime Error';
        }

        // Status lainnya (1=In Queue, 2=Processing, 13=Internal Error, dll.)
        return 'Runtime Error';
    }

    /**
     * Proses submisi kode Mahasiswa ke Judge0, periksa plagiarisme, hitung skor, dan simpan hasilnya.
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitCode(Request $request, $examId, $soalId)
    {
        // 1. VALIDASI INPUT MAHASISWA
        // Memastikan payload request yang masuk memiliki parameter 'code' (source code) dan 'language' (bahasa pemrograman)
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string'
        ]);

        // 2. PENGECEKAN STATUS UJIAN
        // Mengambil data ujian berdasarkan ID dan memanggil checkTimeout() untuk mengecek apakah batas durasi ujian sudah habis.
        // Jika status ujian sudah tidak aktif (karena waktu habis atau di-close), pengiriman jawaban akan langsung ditolak.
        $exam = Ujian::findOrFail($examId);
        $exam->checkTimeout();
        if ($exam->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Ujian tidak aktif atau sudah ditutup karena waktu habis.']);
        }

        // 2b. PENGECEKAN JAWABAN SUDAH BENAR (ACCEPTED)
        // Jika mahasiswa sudah memiliki submisi berstatus 'Accepted', maka tidak boleh submit lagi.
        $hasAccepted = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereIn('status', ['Accepted', 'accepted'])
            ->exists();

        if ($hasAccepted) {
            return response()->json(['success' => false, 'message' => 'Anda sudah menyelesaikan soal ini dengan status Accepted (Benar). Tidak perlu submit lagi.']);
        }

        // 3. PENGHITUNGAN JUMLAH PERCOBAAN (ATTEMPTS) MAHASISWA
        // Menghitung berapa kali mahasiswa tersebut telah mengirimkan jawaban untuk soal terkait dalam ujian ini (yang is_reset = false).
        $attemptsUsed = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('is_reset', false)
            ->count();

        // Jika jumlah percobaan yang telah dilakukan melebihi batas maksimal percobaan yang diizinkan pada ujian ini,
        // maka submit code dibatalkan dan mengembalikan pesan error.
        if ($attemptsUsed >= $exam->max_attempt) {
            return response()->json(['success' => false, 'message' => 'Batas submit maksimal (' . $exam->max_attempt . ' kali) telah tercapai untuk soal ini.']);
        }

        // 4. PENGAMBILAN DATA SOAL & TEST CASES
        // Mengambil data soal beserta seluruh Test Case (input & output pembanding) yang ada di database.
        $soal = \App\Models\Soal::with('testCases')->where('ujian_id', $examId)->findOrFail($soalId);
        $testCases = $soal->testCases;

        // Jika soal tidak memiliki test case, submit code tidak bisa diproses.
        if ($testCases->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Tidak ada test case untuk soal ini.']);
        }

        // 5. PEMETAAN BAHASA KE ID PADA JUDGE0
        // Memetakan input string bahasa pemrograman dari frontend ke ID spesifik bahasa di Judge0 API.
        $langMap = [
            'python' => 71,
            'cpp' => 54,
            'java' => 62,
            'dart' => 90 // Dart 2.19.2 pada Judge0 CE v1.13.0+
        ];
        $languageId = $langMap[$request->language] ?? 71;

        // 6. KONFIGURASI BATAS WAKTU (TIMEOUT) EKSEKUSI
        // Memuat batas waktu eksekusi kode mahasiswa dari file konfigurasi/env.
        $judgeConfig = config('judge');
        $languageSettings = $judgeConfig['language_settings'][$request->language] ?? [];
        $rawTimeout = (float) ($languageSettings['timeout'] ?? 0);

        // Fallback jika nilainya tidak valid, menggunakan timeout global (default 5 detik)
        if ($rawTimeout <= 0 || !is_finite($rawTimeout)) {
            $rawTimeout = (float) ($judgeConfig['execution_time_limit'] ?? 5);
        }
        // Minimal 1.0 detik agar Judge0 tidak error karena limit terlalu kecil
        $executionTimeout = max(1.0, $rawTimeout);

        // 7. KONFIGURASI BATAS MEMORI (MEMORY LIMIT) EKSEKUSI
        // Memuat limit memori eksekusi dalam MegaBytes (MB), lalu mengubahnya ke KiloBytes (KB) untuk dikirim ke Judge0.
        $rawMemoryMB = (float) ($languageSettings['memory_limit'] ?? $judgeConfig['memory_limit'] ?? 256);
        if ($rawMemoryMB <= 0 || !is_finite($rawMemoryMB)) {
            $rawMemoryMB = 256.0;
        }
        // Minimal 32 MB = 32768 KB
        $memoryLimitKB = max(32768, (int) ($rawMemoryMB * 1024));

        // 8. AUTOGRADER: EKSEKUSI KODE PADA API JUDGE0 UNTUK SETIAP TEST CASE
        $judge0Url = 'https://judge0-ce.p.rapidapi.com';
        $rapidApiKey = config('services.judge0.key');

        if (empty($rapidApiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi API Judge0 belum diatur. Silakan masukkan RAPIDAPI_JUDGE0_KEY Anda di file .env.'
            ]);
        }
        
        $allPassed = true; // Flag penanda apakah semua test case berhasil lulus (Accepted)
        $finalStatus = 'Accepted'; // Status akhir kompilasi & eksekusi submisi
        $timeTaken = 0; // Durasi eksekusi tertinggi di antara semua test case (dalam detik)
        $memoryUsed = 0; // Penggunaan memori tertinggi di antara semua test case (dalam KB)
        $testCaseResults = []; // Menyimpan log hasil evaluasi masing-masing test case
        $judge0StatusId = null; // Menyimpan ID status Judge0 secara keseluruhan
        $judge0StatusDescription = null; // Menyimpan deskripsi status Judge0 secara keseluruhan

        // Melakukan perulangan untuk mengevaluasi source code terhadap setiap test case satu per satu
        foreach ($testCases as $idx => $tc) {
            // Menyusun payload data yang di-encode ke Base64 (sesuai standard Judge0 untuk menghindari masalah karakter khusus)
            $payload = [
                'source_code' => base64_encode($request->code),
                'language_id' => $languageId,
                'stdin' => base64_encode($this->normalizeOutput($tc->input ?? '')),
                'expected_output' => base64_encode($this->normalizeOutput($tc->expected_output ?? '')),
                'cpu_time_limit' => $executionTimeout,
                'memory_limit' => $memoryLimitKB,
            ];

            $tcStatus = 'Accepted';
            $stdout = '';
            $stderr = '';
            $tcJudge0StatusId = null;
            $tcJudge0StatusDesc = '';

            try {
                // Mengirim HTTP POST request ke server Judge0 CE dengan parameter wait=true agar eksekusi sinkronus langsung selesai
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // Memaksa cURL menggunakan IPv4 untuk kestabilan koneksi
                        ]
                    ])
                    ->withHeaders([
                        'x-rapidapi-host' => 'judge0-ce.p.rapidapi.com',
                        'x-rapidapi-key' => $rapidApiKey,
                        'Content-Type' => 'application/json',
                    ])->timeout((int) $judgeConfig['submission_timeout'] ?? 30)
                      ->post("{$judge0Url}/submissions?base64_encoded=true&wait=true", $payload);

                // Jika respons dari server Judge0 tidak sukses (HTTP Status != 2xx)
                if (!$response->successful()) {
                    $httpStatus = $response->status();
                    $httpBody = $response->body();
                    $allPassed = false;
                    $tcStatus = 'Runtime Error';
                    $finalStatus = 'Runtime Error';
                    $stderr = "Judge0 HTTP {$httpStatus}: {$httpBody}";
                    $testCaseResults[] = [
                        'index' => $idx + 1,
                        'status' => $tcStatus,
                        'input' => $tc->input,
                        'expected_output' => $tc->expected_output,
                        'stdout' => '',
                        'stderr' => $stderr,
                    ];
                    continue;
                }

                $result = $response->json();

                // Cek apakah response JSON memiliki field status
                if (!isset($result['status'])) {
                    $finalStatus = 'Runtime Error';
                    $tcStatus = 'Runtime Error';
                    $allPassed = false;
                    $stderr = 'Respons Judge0 tidak valid (tidak ada field status).';
                } else {
                    $tcJudge0StatusId = (int) $result['status']['id'];
                    $tcJudge0StatusDesc = $result['status']['description'] ?? '';

                    // Set status pertama sebagai status referensi global jika masih kosong
                    if ($judge0StatusId === null) {
                        $judge0StatusId = $tcJudge0StatusId;
                        $judge0StatusDescription = $tcJudge0StatusDesc;
                    }

                    // Decode output (stdout, stderr, compile_output) dari Base64 ke string biasa
                    $stdout = isset($result['stdout']) ? base64_decode($result['stdout']) : '';
                    $stderr = isset($result['stderr']) ? base64_decode($result['stderr']) : '';
                    $compile_output = isset($result['compile_output']) ? base64_decode($result['compile_output']) : '';

                    // Normalisasi output untuk membersihkan whitespace dan newline di baris terakhir
                    $stdout = $this->normalizeOutput($stdout);

                    // Gabungkan pesan error kompilasi ke stderr jika stderr biasa kosong
                    if (trim($stderr) === '' && trim($compile_output) !== '') {
                        $stderr = trim($compile_output);
                    } else {
                        $stderr = trim($stderr);
                    }

                    // Catat statistik waktu & memori maksimal yang dikonsumsi
                    $timeTaken = max($timeTaken, floatval($result['time'] ?? 0));
                    $memoryUsed = max($memoryUsed, floatval($result['memory'] ?? 0));

                    // Memanggil helper resolveJudge0StatusForEvalcode() untuk mengonversi status ID Judge0 ke kategori status EvalCode
                    $resolvedStatus = $this->resolveJudge0StatusForEvalcode(
                        $tcJudge0StatusId,
                        $tcJudge0StatusDesc,
                        $compile_output,
                        $stderr
                    );

                    // Pengecekan status kelulusan pada masing-masing test case
                    if ($resolvedStatus === 'Accepted') {
                        // Verifikasi manual tambahan: mencocokkan stdout hasil eksekusi dengan expected output soal
                        $expected = $this->normalizeOutput($tc->expected_output ?? '');
                        if ($stdout !== $expected) {
                            $allPassed = false;
                            $tcStatus = 'Wrong Answer';
                            if ($finalStatus === 'Accepted') {
                                $finalStatus = 'Wrong Answer';
                            }
                        }
                    } elseif ($resolvedStatus === 'Wrong Answer') {
                        $allPassed = false;
                        $tcStatus = 'Wrong Answer';
                        if ($finalStatus === 'Accepted') {
                            $finalStatus = 'Wrong Answer';
                        }
                    } elseif ($resolvedStatus === 'Time Limit Exceeded') {
                        $allPassed = false;
                        $tcStatus = 'Time Limit Exceeded';
                        if (in_array($finalStatus, ['Accepted', 'Wrong Answer'])) {
                            $finalStatus = 'Time Limit Exceeded';
                        }
                    } elseif ($resolvedStatus === 'Compilation Error') {
                        $allPassed = false;
                        $tcStatus = 'Compilation Error';
                        $finalStatus = 'Compilation Error';

                        // Mengoverride status global ke Compilation Error agar tampil konsisten
                        $judge0StatusId = $tcJudge0StatusId;
                        $judge0StatusDescription = $tcJudge0StatusDesc;
                    } else {
                        // Status default: Runtime Error
                        $allPassed = false;
                        $tcStatus = 'Runtime Error';
                        $finalStatus = 'Runtime Error';

                        $judge0StatusId = $tcJudge0StatusId;
                        $judge0StatusDescription = $tcJudge0StatusDesc;
                    }
                }
            } catch (\Exception $e) {
                // Logging error jika terjadi exception saat memanggil endpoint API
                \Log::error('Judge0 Error: ' . $e->getMessage());
                $finalStatus = 'Runtime Error';
                $tcStatus = 'Runtime Error';
                $allPassed = false;
                $stderr = 'Gagal menghubungi server eksekusi Judge0: ' . $e->getMessage();
            }

            // Memasukkan log detail per test case ke array hasil
            $testCaseResults[] = [
                'index' => $idx + 1,
                'status' => $tcStatus,
                'input' => $tc->input,
                'expected_output' => $tc->expected_output,
                'stdout' => $stdout,
                'stderr' => $stderr,
            ];
        }

        // 9. PENENTUAN SKOR AKHIR SUBMISI
        // Jika seluruh test case lulus (allPassed = true), berikan skor penuh sesuai bobot nilai soal.
        // Jika ada test case yang gagal, maka skor akhir submisi ini adalah 0.
        $skor = $allPassed ? $soal->bobot_nilai : 0;
        $similarityIndex = null;

        // 10. DETEKSI PLAGIARISME DENGAN ALGORITMA JACCARD SIMILARITY
        // Deteksi plagiarisme hanya dilakukan apabila source code mahasiswa lulus (Accepted) dan fitur plagiarism detection diaktifkan.
        if ($allPassed && config('judge.enable_plagiarism_detection', true)) {
            $similarityIndex = $this->calculatePlagiarism($request->code, $soalId);
        }

        // 11. PENYIMPANAN LOG SUBMISI KE DATABASE
        // Menyimpan riwayat hasil pengerjaan mahasiswa ke tabel 'submissions'
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'soal_id' => $soalId,
            'source_code' => $request->code,
            'skor' => $skor,
            'status' => $finalStatus,
            'similarity_index' => $similarityIndex, // Nilai persentase kesamaan/plagiarisme
            'execution_time' => $timeTaken . 's',
            'memory' => $memoryUsed . ' KB',
            'is_reset' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 12. MENGEMBALIKAN HASIL EVALUASI DALAM FORMAT JSON
        // Data ini akan dikonsumsi oleh frontend (workspace coding mahasiswa) untuk menampilkan feedback langsung.
        return response()->json([
            'success' => true,
            'status' => $finalStatus,
            'judge0_status_id' => $judge0StatusId,
            'judge0_status_description' => $judge0StatusDescription,
            'time' => $timeTaken . 's',
            'memory' => $memoryUsed . ' KB',
            'testCases' => $testCaseResults,
            'similarity' => $similarityIndex !== null ? round($similarityIndex, 2) . '%' : null,
            'attempts_used' => $attemptsUsed + 1,
        ]);
    }

    private function calculatePlagiarism($newCode, $soalId)
    {
        // Get all accepted submissions for this soal from other users
        $otherSubmissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('status', 'Accepted')
            ->where('user_id', '!=', \Illuminate\Support\Facades\Auth::id())
            ->get();

        if ($otherSubmissions->isEmpty()) {
            return 0; // No other submissions to compare
        }

        $maxSimilarity = 0;

        foreach ($otherSubmissions as $sub) {
            $similarity = $this->plagiarismService->calculateSimilarity($newCode, $sub->source_code);
            if ($similarity > $maxSimilarity) {
                $maxSimilarity = $similarity;
            }
        }

        return $maxSimilarity;
    }

    /**
     * Endpoint untuk memeriksa status ujian secara real-time.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($id)
    {
        $exam = Ujian::findOrFail($id);
        $exam->checkTimeout();
        
        return response()->json([
            'status' => $exam->status,
            'remainingSeconds' => $exam->getRemainingSeconds()
        ]);
    }
}
