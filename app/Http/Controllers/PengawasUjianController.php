<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Token;

class PengawasUjianController extends Controller
{
    /**
     * Dashboard: show all ujians grouped by status
     */
    public function dashboard()
    {
        $activeExams = Ujian::where('status', 'active')->orderBy('updated_at', 'desc')->get();
        $closedExams = Ujian::where('status', 'closed')->orderBy('updated_at', 'desc')->get();
        $finishedExams = Ujian::where('status', 'finished')->orderBy('updated_at', 'desc')->get();

        return view('pengawas.dashboard', [
            'activeExams' => $activeExams,
            'closedExams' => $closedExams,
            'finishedExams' => $finishedExams,
        ]);
    }

    /**
     * Detail monitoring for a specific exam
     */
    public function detail(Request $request, $id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);

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
                \Illuminate\Support\Facades\DB::raw("MAX(CASE WHEN submissions.justification_note IS NULL THEN submissions.similarity_index ELSE NULL END) as max_similarity_soal")
            )
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $query = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select('users.name', 'users.nim_nip', 'users.user_id', \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'), \Illuminate\Support\Facades\DB::raw('SUM(sub.is_accepted) as accepted_count'), \Illuminate\Support\Facades\DB::raw('MAX(sub.max_similarity_soal) as highest_similarity'));

        $participants = $query->groupBy('users.name', 'users.nim_nip', 'users.user_id')
            ->orderByDesc('total_skor')
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

        // Add progress percentage to participants
        foreach ($participants as $p) {
            $p->progress_percentage = $totalSoal > 0 ? ($p->accepted_count / $totalSoal) * 100 : 0;
            $scorePercentage = $maxScore > 0 ? ($p->total_skor / $maxScore) * 100 : 0;
            $p->status = ($p->total_skor ?? 0) >= $passingGrade ? 'Lulus' : 'Tidak Lulus';
            $p->submissions = $allSubmissions->get($p->user_id) ?? collect([]);
        }

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

    public function overrideScore(Request $request, $submissionId)
    {
        $request->validate([
            'skor' => 'required|numeric|min:0',
            'justification_note' => 'required|string|max:500',
        ]);

        \Illuminate\Support\Facades\DB::table('submissions')->where('submission_id', $submissionId)->update([
            'skor' => $request->skor,
            'justification_note' => $request->justification_note,
        ]);

        return back()->with('success', 'Skor submisi berhasil di-override.');
    }

    /**
     * Start an exam: set status to active & generate first token
     */
    public function startExam($id)
    {
        $exam = Ujian::findOrFail($id);

        if ($exam->status === 'finished') {
            return redirect()->back()->with('error', 'Ujian yang sudah selesai tidak bisa dimulai lagi.');
        }

        $exam->update(['status' => 'active']);

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
     * Pause an exam: set status to closed & deactivate tokens
     */
    public function endExam($id)
    {
        $exam = Ujian::findOrFail($id);
        $exam->update(['status' => 'closed']);

        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Ujian berhasil di-pause.');
    }

    /**
     * Finish an exam permanently: set status to finished & deactivate tokens
     */
    public function finishExam($id)
    {
        $exam = Ujian::findOrFail($id);
        $exam->update(['status' => 'finished']);

        Token::where('ujian_id', $exam->ujian_id)->update(['status_aktif' => false]);

        return redirect()->back()->with('success', 'Ujian berhasil diakhiri secara permanen.');
    }

    /**
     * API endpoint: refresh token (called by JS every 60 seconds)
     * Returns JSON with new token and timestamp
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
     * API endpoint: get current active token (for polling)
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

    public function soal($examId, $soalId)
    {
        $exam = Ujian::findOrFail($examId);
        $soal = \App\Models\Soal::with('testCases')->findOrFail($soalId);

        return view('pengawas.ujian.soal', [
            'exam' => $exam,
            'soal' => $soal,
        ]);
    }

    public function password()
    {
        return view('pengawas.password');
    }
}
