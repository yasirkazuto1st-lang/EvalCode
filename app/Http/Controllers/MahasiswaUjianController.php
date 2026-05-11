<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Token;

class MahasiswaUjianController extends Controller
{
    public function dashboard()
    {
        $activeExams = Ujian::where('status', 'active')->orderBy('updated_at', 'desc')->get();
        $closedExams = Ujian::where('status', 'closed')->orderBy('updated_at', 'desc')->get();
        $finishedExams = Ujian::where('status', 'finished')->orderBy('updated_at', 'desc')->get();
        return view('mahasiswa.dashboard', compact('activeExams', 'closedExams', 'finishedExams'));
    }

    public function detail($id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);
        
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

        foreach ($exam->soals as $soal) {
            $bestSubmission = $userSubmissions->where('soal_id', $soal->soal_id)->sortByDesc('skor')->first();
            if ($bestSubmission) {
                $soal->status_pengerjaan = $bestSubmission->status;
                $soal->skor_tertinggi = $bestSubmission->skor;
                if ($soal->status_pengerjaan == 'Accepted') {
                    $acceptedCount++;
                }
            } else {
                $soal->status_pengerjaan = 'Belum Dikerjakan';
                $soal->skor_tertinggi = 0;
            }
        }

        return view('mahasiswa.ujian.detail', compact('exam', 'leaderboard', 'acceptedCount'));
    }

    public function joinExam(Request $request, $id)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $exam = Ujian::findOrFail($id);

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

    public function workspace($examId, $soalId)
    {
        $exam = Ujian::with('soals')->findOrFail($examId);
        
        if ($exam->status !== 'active') {
            return redirect()->route('dashboard')->with('error', 'Ujian tidak aktif.');
        }

        $soal = \App\Models\Soal::with('testCases')->where('ujian_id', $examId)->findOrFail($soalId);
        
        return view('mahasiswa.workspace', compact('exam', 'soal'));
    }
    public function submitCode(Request $request, $examId, $soalId)
    {
        $request->validate([
            'code' => 'required|string',
            'language' => 'required|string'
        ]);

        $exam = Ujian::findOrFail($examId);
        if ($exam->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Ujian tidak aktif']);
        }

        $soal = \App\Models\Soal::with('testCases')->where('ujian_id', $examId)->findOrFail($soalId);
        $testCases = $soal->testCases;

        if ($testCases->count() === 0) {
            return response()->json(['success' => false, 'message' => 'Tidak ada test case untuk soal ini.']);
        }

        $langMap = [
            'python' => 71,
            'cpp' => 54,
            'java' => 62,
            'dart' => 28 // Approximate/Dummy if not supported
        ];
        $languageId = $langMap[$request->language] ?? 71;

        $judge0Url = 'https://judge0-ce.p.rapidapi.com';
        $rapidApiKey = env('RAPIDAPI_JUDGE0_KEY', '050f745abemsh17cd274a2c49738p1b2dc6jsn51f733269ced');
        
        $allPassed = true;
        $finalStatus = 'Accepted';
        $timeTaken = 0;
        $memoryUsed = 0;
        $testCaseResults = [];

        foreach ($testCases as $idx => $tc) {
            $payload = [
                'source_code' => base64_encode($request->code),
                'language_id' => $languageId,
                'stdin' => base64_encode($tc->input),
                'expected_output' => base64_encode($tc->expected_output),
            ];

            $tcStatus = 'Accepted';
            $stdout = '';
            $stderr = '';

            try {
                // Submit to Judge0 via RapidAPI
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'x-rapidapi-host' => 'judge0-ce.p.rapidapi.com',
                    'x-rapidapi-key' => $rapidApiKey,
                    'Content-Type' => 'application/json',
                ])->post("{$judge0Url}/submissions?base64_encoded=true&wait=true", $payload);
                
                $result = $response->json();

                if (!isset($result['status'])) {
                    $finalStatus = 'Runtime Error';
                    $tcStatus = 'Runtime Error';
                    $allPassed = false;
                } else {
                    $statusId = $result['status']['id'];
                    $stdout = isset($result['stdout']) ? trim(base64_decode($result['stdout'])) : '';
                    $stderr = isset($result['stderr']) ? trim(base64_decode($result['stderr'])) : '';
                    $compile_output = isset($result['compile_output']) ? trim(base64_decode($result['compile_output'])) : '';
                    
                    if ($stderr == '' && $compile_output != '') {
                        $stderr = $compile_output;
                    }

                    $timeTaken = max($timeTaken, floatval($result['time'] ?? 0));
                    $memoryUsed = max($memoryUsed, floatval($result['memory'] ?? 0));

                    if ($statusId === 3) {
                        // Accepted by Judge0 (Output matches expected_output)
                        // But we will manually verify anyway just to be safe
                        $expected = trim($tc->expected_output);
                        if ($stdout !== $expected) {
                            $allPassed = false;
                            $tcStatus = 'Wrong Answer';
                            if ($finalStatus === 'Accepted') $finalStatus = 'Wrong Answer';
                        }
                    } elseif ($statusId === 4) {
                        $allPassed = false;
                        $tcStatus = 'Wrong Answer';
                        if ($finalStatus === 'Accepted') $finalStatus = 'Wrong Answer';
                    } elseif ($statusId === 5) {
                        $allPassed = false;
                        $tcStatus = 'Time Limit Exceeded';
                        if (in_array($finalStatus, ['Accepted', 'Wrong Answer'])) $finalStatus = 'Time Limit Exceeded';
                    } else {
                        $allPassed = false;
                        $tcStatus = 'Runtime Error';
                        $finalStatus = 'Runtime Error';
                    }
                }
            } catch (\Exception $e) {
                // Mock judge0 if it's not running
                \Log::error('Judge0 Error: ' . $e->getMessage());
                $finalStatus = 'Runtime Error';
                $tcStatus = 'Runtime Error';
                $allPassed = false;
                $stderr = 'Gagal menghubungi server eksekusi Judge0. Pastikan server Judge0 aktif.';
            }

            $testCaseResults[] = [
                'index' => $idx + 1,
                'status' => $tcStatus,
                'input' => $tc->input,
                'expected_output' => $tc->expected_output,
                'stdout' => $stdout,
                'stderr' => $stderr
            ];
        }

        $skor = $allPassed ? $soal->bobot_nilai : 0;
        $similarityIndex = null;

        if ($allPassed) {
            $similarityIndex = $this->calculatePlagiarism($request->code, $soalId);
        }

        // Save submission
        \Illuminate\Support\Facades\DB::table('submissions')->insert([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'soal_id' => $soalId,
            'source_code' => $request->code,
            'skor' => $skor,
            'status' => $finalStatus,
            'similarity_index' => $similarityIndex,
            'execution_time' => $timeTaken . 's',
            'memory' => $memoryUsed . ' KB',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'status' => $finalStatus,
            'time' => $timeTaken . 's',
            'memory' => $memoryUsed . ' KB',
            'testCases' => $testCaseResults,
            'similarity' => $similarityIndex !== null ? round($similarityIndex, 2) . '%' : null
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
        $tokens1 = $this->tokenizeCode($newCode);

        foreach ($otherSubmissions as $sub) {
            $tokens2 = $this->tokenizeCode($sub->source_code);
            $intersection = count(array_intersect($tokens1, $tokens2));
            $union = count(array_unique(array_merge($tokens1, $tokens2)));
            
            if ($union > 0) {
                $similarity = ($intersection / $union) * 100;
                if ($similarity > $maxSimilarity) {
                    $maxSimilarity = $similarity;
                }
            }
        }

        return $maxSimilarity;
    }

    private function tokenizeCode($code)
    {
        // Trimming comments
        $code = preg_replace('!/\*.*?\*/!s', '', $code); // multi line comments
        $code = preg_replace('/#.*/', '', $code); // python comments
        $code = preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $code); // single line comments

        // Tokenize by word characters and some symbols
        preg_match_all('/[a-zA-Z0-9_]+|[{}\[\]()=+\-*\/<>;.]/', $code, $matches);
        
        return array_unique($matches[0]);
    }
}
