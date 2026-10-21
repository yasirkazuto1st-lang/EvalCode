<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Token;

class PengawasUjianController extends Controller
{
    /**
     * Dashboard: Tampilkan daftar seluruh ujian yang dikelompokkan berdasarkan statusnya.
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $activeExams = \Illuminate\Support\Facades\Cache::remember('active_exams_with_count', 10, function () {
            $list = Ujian::where('status', 'active')->orderBy('updated_at', 'desc')->get();
            foreach ($list as $exam) {
                $exam->checkTimeout();
            }
            return Ujian::withCount('soals')->where('status', 'active')->orderBy('updated_at', 'desc')->get();
        });

        $closedExams = \Illuminate\Support\Facades\Cache::remember('closed_exams_with_count', 10, function () {
            return Ujian::withCount('soals')->where('status', 'closed')->orderBy('updated_at', 'desc')->get();
        });

        $finishedExams = \Illuminate\Support\Facades\Cache::remember('finished_exams_with_count', 10, function () {
            return Ujian::withCount('soals')->where('status', 'finished')->orderBy('updated_at', 'desc')->get();
        });

        return view('pengawas.dashboard', [
            'activeExams' => $activeExams,
            'closedExams' => $closedExams,
            'finishedExams' => $finishedExams,
        ]);
    }

    /**
     * Halaman detail monitoring pengawas untuk sebuah ujian tertentu.
     * Mengambil data token aktif, peserta, status kelulusan, dan statistik soal.
     * 
     * @param Request $request
     * @param int $id ID Ujian
     * @return \Illuminate\View\View
     */
    public function detail(Request $request, $id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);
        $exam->checkTimeout();

        // Get active token for this exam
        $activeToken = Token::where('ujian_id', $exam->ujian_id)
            ->where('status_aktif', true)
            ->latest()
            ->first();

        // Calculate Real Participants Data
        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select(
                'submissions.user_id', 
                'submissions.soal_id', 
                \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'), 
                \Illuminate\Support\Facades\DB::raw("MAX(CASE WHEN submissions.status = 'Accepted' THEN 1 ELSE 0 END) as is_accepted"), 
                \Illuminate\Support\Facades\DB::raw("MAX(CASE WHEN submissions.justification_note IS NULL THEN submissions.similarity_index ELSE NULL END) as max_similarity_soal"),
                \Illuminate\Support\Facades\DB::raw("MIN(CASE WHEN LOWER(submissions.status) = 'accepted' OR submissions.skor > 0 THEN submissions.created_at ELSE NULL END) as first_correct_at"),
                \Illuminate\Support\Facades\DB::raw('MAX(submissions.created_at) as last_submission_at'),
                \Illuminate\Support\Facades\DB::raw('MIN(submissions.created_at) as first_submission_at')
            )
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $query = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select(
                'users.name', 
                'users.nim_username', 
                'users.user_id', 
                \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'), 
                \Illuminate\Support\Facades\DB::raw('SUM(sub.is_accepted) as accepted_count'), 
                \Illuminate\Support\Facades\DB::raw('MAX(sub.max_similarity_soal) as highest_similarity'),
                \Illuminate\Support\Facades\DB::raw('MAX(sub.first_correct_at) as last_correct_at'),
                \Illuminate\Support\Facades\DB::raw('MAX(sub.last_submission_at) as last_submission_at'),
                \Illuminate\Support\Facades\DB::raw('MIN(sub.first_submission_at) as first_submission_at')
            );

        $participants = $query->groupBy('users.name', 'users.nim_username', 'users.user_id')
            ->get();

        $totalSoal = $exam->soals->count();
        $maxScore = $exam->soals->sum('bobot_nilai');
        $passingGrade = $exam->passing_grade;

        $allSubmissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.*', 'soals.nama_soal', 'soals.bobot_nilai')
            ->orderByDesc('submissions.created_at')
            ->get()
            ->groupBy('user_id');

        $examStart = $exam->getOfficialStartTime();
        $maxDurationSeconds = (int) ($exam->durasi * 60);
        $sessionCount = \App\Models\UjianSession::where('ujian_id', $exam->ujian_id)->count();

        // Add progress percentage, pass status, and total execution time for tie-breaker
        foreach ($participants as $p) {
            $p->progress_percentage = $totalSoal > 0 ? ($p->accepted_count / $totalSoal) * 100 : 0;
            $scorePercentage = $maxScore > 0 ? ($p->total_skor / $maxScore) * 100 : 0;
            $p->status = ($p->total_skor ?? 0) >= $passingGrade ? 'Lulus' : 'Tidak Lulus';
            $p->submissions = $allSubmissions->get($p->user_id) ?? collect([]);

            if ($p->total_skor > 0) {
                $lastTimeStr = $p->last_correct_at ?: $p->last_submission_at;
                if ($lastTimeStr) {
                    $lastTime = \Carbon\Carbon::parse($lastTimeStr);
                    $firstActivity = $p->first_submission_at ? \Carbon\Carbon::parse($p->first_submission_at) : $lastTime;
                    $sessionStart = $exam->getOfficialStartTime($firstActivity);
                    $startTime = ($sessionStart && $sessionStart->lte($firstActivity))
                        ? $sessionStart
                        : $firstActivity;
                    if ($sessionCount <= 1 && $startTime->lt($firstActivity->copy()->subSeconds($maxDurationSeconds))) {
                        $startTime = $firstActivity;
                    }
                    $p->total_waktu_detik = (int) abs($lastTime->diffInSeconds($startTime));
                    $p->total_waktu_formatted = \App\Models\Ujian::formatWaktuPengerjaan($p->total_waktu_detik);
                } else {
                    $p->total_waktu_detik = null;
                    $p->total_waktu_formatted = '-';
                }
            } else {
                $p->total_waktu_detik = null;
                $p->total_waktu_formatted = '-';
            }
        }

        // Sorting Tie-Breaker untuk Pengawas
        $participants = $participants->sort(function ($a, $b) {
            if ($b->total_skor !== $a->total_skor) {
                return $b->total_skor <=> $a->total_skor;
            }
            $wA = $a->total_waktu_detik ?? PHP_INT_MAX;
            $wB = $b->total_waktu_detik ?? PHP_INT_MAX;
            if ($wA !== $wB) {
                return $wA <=> $wB;
            }
            if ($b->accepted_count !== $a->accepted_count) {
                return $b->accepted_count <=> $a->accepted_count;
            }
            return strcmp($a->name, $b->name);
        })->values();

        // Calculate Soal Statistics
        $soalStats = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('status', 'Accepted')
            ->select('soal_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT user_id) as success_count'))
            ->groupBy('soal_id')
            ->get()
            ->keyBy('soal_id');

        $totalParticipants = $participants->count();

        foreach ($exam->soals as $soal) {
            $stat = $soalStats->get($soal->soal_id);
            $soal->success_count = $stat ? $stat->success_count : 0;
            $soal->success_percentage = $totalParticipants > 0 ? ($soal->success_count / $totalParticipants) * 100 : 0;
        }

        return view('pengawas.ujian.detail', [
            'exam' => $exam,
            'activeToken' => $activeToken,
            'participants' => $participants,
            'totalParticipants' => $totalParticipants
        ]);
    }

    /**
     * Tampilkan riwayat submisi seorang peserta ujian secara lengkap (terbaru di atas).
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @param int $userId ID User (Mahasiswa)
     * @return \Illuminate\View\View
     */
    public function pesertaRiwayat(Request $request, $examId, $userId)
    {
        $exam = Ujian::with('soals')->findOrFail($examId);
        $user = \App\Models\User::findOrFail($userId);

        $submissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $examId)
            ->where('submissions.user_id', $userId)
            ->select('submissions.*', 'soals.nama_soal', 'soals.bobot_nilai')
            ->orderByDesc('submissions.created_at')
            ->get();

        return view('pengawas.ujian.riwayat_peserta', compact('exam', 'user', 'submissions'));
    }

    /**
     * Pengawas dapat menimpa (override) skor otomatis dengan skor manual beserta catatannya.
     * 
     * @param Request $request
     * @param int $submissionId ID Submisi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function overrideScore(Request $request, $submissionId)
    {
        $request->validate([
            'skor' => 'required|integer|min:0',
            'justification_note' => 'required|string|max:500',
        ]);

        \Illuminate\Support\Facades\DB::table('submissions')->where('submission_id', $submissionId)->update([
            'skor' => $request->skor,
            'justification_note' => $request->justification_note,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Skor submisi berhasil di-override.',
                'submission_id' => $submissionId
            ]);
        }

        return back()->with('success', 'Skor submisi berhasil di-override.');
    }

    /**
     * Hapus submisi peserta ujian.
     * 
     * @param Request $request
     * @param int $submissionId ID Submisi
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroySubmission(Request $request, $submissionId)
    {
        \Illuminate\Support\Facades\DB::table('submissions')->where('submission_id', $submissionId)->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Submisi berhasil dihapus.',
                'submission_id' => $submissionId
            ]);
        }

        return back()->with('success', 'Submisi berhasil dihapus.');
    }

    /**
     * Mulai ujian: ubah status ujian menjadi aktif dan hasilkan token pertama.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startExam($id)
    {
        $exam = Ujian::findOrFail($id);

        if ($exam->status === 'finished') {
            return redirect()->back()->with('error', 'Ujian yang sudah selesai tidak bisa dimulai lagi.');
        }

        // Selalu reset ke durasi penuh setiap kali mulai
        $exam->sisa_waktu = $exam->durasi * 60;
        $exam->started_at = \Carbon\Carbon::now();
        $exam->status = 'active';
        $exam->save();

        // Tutup sesi sebelumnya yang mungkin belum tertutup
        \App\Models\UjianSession::where('ujian_id', $exam->ujian_id)
            ->whereNull('ended_at')
            ->update(['ended_at' => $exam->started_at]);

        // Buat sesi ujian baru di tabel ujian_sessions
        $lastSession = \App\Models\UjianSession::where('ujian_id', $exam->ujian_id)->orderByDesc('sesi_ke')->first();
        \App\Models\UjianSession::create([
            'ujian_id' => $exam->ujian_id,
            'sesi_ke' => $lastSession ? ($lastSession->sesi_ke + 1) : 1,
            'started_at' => $exam->started_at,
        ]);

        // Deactivate all old tokens
        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        // Create new token
        Token::create([
            'ujian_id' => $exam->ujian_id,
            'kode_token' => Token::generateCode(),
            'status_aktif' => true,
        ]);

        return redirect()->back()->with('success', 'Ujian berhasil dimulai!');
    }

    /**
     * Hentikan sementara ujian: ubah status menjadi closed & nonaktifkan token.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function endExam($id)
    {
        $exam = Ujian::findOrFail($id);

        // Reset sisa_waktu ke 0 saat pause, namun pertahankan started_at agar riwayat pengerjaan tidak hilang
        $exam->sisa_waktu = 0;
        $exam->status = 'closed';
        $exam->save();

        // Tutup sesi aktif di tabel ujian_sessions
        \App\Models\UjianSession::where('ujian_id', $exam->ujian_id)
            ->whereNull('ended_at')
            ->update(['ended_at' => \Carbon\Carbon::now()]);

        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Ujian berhasil di-pause.');
    }

    /**
     * Selesaikan ujian permanen: ubah status menjadi finished & nonaktifkan token.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finishExam($id)
    {
        $exam = Ujian::findOrFail($id);
        
        $exam->sisa_waktu = 0;
        $exam->status = 'finished';
        $exam->save();

        // Tutup sesi aktif di tabel ujian_sessions
        \App\Models\UjianSession::where('ujian_id', $exam->ujian_id)
            ->whereNull('ended_at')
            ->update(['ended_at' => \Carbon\Carbon::now()]);

        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Ujian berhasil diakhiri secara permanen.');
    }

    /**
     * Reset attempt limit untuk mahasiswa pada soal tertentu.
     * 
     * @param int $examId ID Ujian
     * @param int $userId ID User
     * @param int $soalId ID Soal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetAttempts($examId, $userId, $soalId)
    {
        $exam = Ujian::findOrFail($examId);

        \Illuminate\Support\Facades\DB::table('submissions')
            ->where('user_id', $userId)
            ->where('soal_id', $soalId)
            ->update([
                'is_reset' => true,
                'updated_at' => now()
            ]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kesempatan submit mahasiswa untuk soal ini berhasil di-reset.'
            ]);
        }

        return redirect()->back()->with('success', 'Kesempatan submit mahasiswa untuk soal ini berhasil di-reset.');
    }

    /**
     * Endpoint API: Menghasilkan token baru untuk ujian (dipanggil melalui AJAX setiap 60 detik).
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken($id)
    {
        $exam = Ujian::findOrFail($id);

        // Only refresh if exam is active
        if ($exam->status !== 'active') {
            return response()->json(['error' => 'Ujian tidak aktif'], 400);
        }

        // Deactivate old tokens
        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        // Generate new token
        $token = Token::create([
            'ujian_id' => $exam->ujian_id,
            'kode_token' => Token::generateCode(),
            'status_aktif' => true,
        ]);

        return response()->json([
            'token' => $token->kode_token,
            'created_at' => $token->created_at->toISOString(),
        ]);
    }

    /**
     * Endpoint API: Mengambil token yang saat ini sedang aktif (digunakan untuk sinkronisasi antarmuka).
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentToken($id)
    {
        $exam = Ujian::findOrFail($id);

        $token = Token::where('ujian_id', $exam->ujian_id)
            ->where('status_aktif', true)
            ->latest()
            ->first();

        if (!$token) {
            return response()->json(['token' => null, 'created_at' => null]);
        }

        return response()->json([
            'token' => $token->kode_token,
            'created_at' => $token->created_at->toISOString(),
        ]);
    }

    /**
     * Tampilkan halaman deskripsi soal beserta test case input/output.
     * 
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\View\View
     */
    public function soal($examId, $soalId)
    {
        $exam = Ujian::findOrFail($examId);
        $soal = \App\Models\Soal::with('testCases')->findOrFail($soalId);

        return view('pengawas.ujian.soal', [
            'exam' => $exam,
            'soal' => $soal,
        ]);
    }

    /**
     * Tampilkan halaman form ganti password untuk Pengawas.
     * 
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('pengawas.password');
    }
}
