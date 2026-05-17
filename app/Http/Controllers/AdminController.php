<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ujian;
use App\Models\Soal;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // ==========================================
    // DASHBOARD
    // ==========================================
    public function dashboard()
    {
        $stats = (object) [
            'total_ujian' => Ujian::count(),
            'total_admin' => User::where('role', 'Admin')->count(),
            'total_pengawas' => User::where('role', 'Pengawas')->count(),
            'total_mahasiswa' => User::where('role', 'Mahasiswa')->count(),
            'active_ujian' => Ujian::where('status', 'active')->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function password()
    {
        return view('admin.password');
    }

    // ==========================================
    // UJIAN CRUD
    // ==========================================
    public function exams()
    {
        $exams = Ujian::orderBy('created_at', 'desc')->get();
        return view('admin.ujian.index', ['exams' => $exams]);
    }

    public function storeExam(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'passing_grade' => 'required|numeric|min:0',
        ]);

        Ujian::create([
            'user_id' => Auth::user()->user_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'durasi' => $request->durasi,
            'passing_grade' => $request->passing_grade,
        ]);

        return redirect()->route('admin.ujian.index')->with('success', 'Ujian berhasil ditambahkan.');
    }

    public function updateExam(Request $request, $id)
    {
        $exam = Ujian::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'passing_grade' => 'required|numeric|min:0',
            'status' => 'nullable|in:active,closed,finished',
        ]);

        $exam->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'durasi' => $request->durasi,
            'passing_grade' => $request->passing_grade,
            'status' => $request->status ?? $exam->status,
        ]);

        return redirect()->route('admin.ujian.index')->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroyExam($id)
    {
        $exam = Ujian::findOrFail($id);
        // Delete associated soal PDFs from storage
        foreach ($exam->soals as $soal) {
            if ($soal->soal_pdf) {
                Storage::disk('public')->delete($soal->soal_pdf);
            }
        }
        $exam->delete();
        return redirect()->back()->with('success', 'Ujian berhasil dihapus.');
    }

    // Detail of a specific exam
    public function examDetail($id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);
        $questions = $exam->soals;

        // Calculate Real Participants Data
        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.user_id', 'submissions.soal_id', \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'), \Illuminate\Support\Facades\DB::raw("MAX(CASE WHEN submissions.status = 'Accepted' THEN 1 ELSE 0 END) as is_accepted"))
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $query = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select('users.name', 'users.nim_nip', 'users.user_id', \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'), \Illuminate\Support\Facades\DB::raw('SUM(sub.is_accepted) as accepted_count'));

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

        return view('admin.ujian.detail', [
            'exam' => $exam,
            'questions' => $questions,
            'participants' => $participants
        ]);
    }

    public function pesertaRiwayat($examId, $userId)
    {
        $exam = Ujian::with('soals')->findOrFail($examId);
        $user = \App\Models\User::findOrFail($userId);
        
        $submissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $examId)
            ->where('submissions.user_id', $userId)
            ->select('submissions.*', 'soals.nama_soal', 'soals.bobot_nilai')
            ->orderBy('submissions.created_at', 'desc')
            ->get();

        return view('admin.ujian.riwayat_peserta', compact('exam', 'user', 'submissions'));
    }

    public function exportExcel($id)
    {
        $exam = Ujian::findOrFail($id);

        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.user_id', \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'))
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $participants = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select('users.name', 'users.nim_nip', \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'))
            ->where('users.role', 'mahasiswa')
            ->groupBy('users.name', 'users.nim_nip', 'users.user_id')
            ->orderByDesc('total_skor')
            ->get();

        $maxScore = $exam->soals->sum('bobot_nilai');
        $passingGrade = $exam->passing_grade;

        $exportData = [];
        foreach ($participants as $p) {
            $scorePercentage = $maxScore > 0 ? ($p->total_skor / $maxScore) * 100 : 0;
            $status = ($p->total_skor ?? 0) >= $passingGrade ? 'Lulus' : 'Tidak Lulus';
            
            $exportData[] = [
                'NIM' => $p->nim_nip,
                'Nama Mahasiswa' => $p->name,
                'Status' => $status,
                'Skor' => $p->total_skor ?? 0
            ];
        }

        $examInfo = [
            'judul' => $exam->judul,
            'durasi' => $exam->durasi . " Menit",
            'passing_grade' => $exam->passing_grade . " Pts",
            'tanggal' => date('d-m-Y H:i')
        ];

        $filename = "Laporan_Ujian_{$exam->ujian_id}_" . date('Ymd_His') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UjianExport($exportData, $examInfo), $filename);
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

    // ==========================================
    // SOAL CRUD
    // ==========================================
    public function storeSoal(Request $request, $examId)
    {
        $exam = Ujian::findOrFail($examId);

        $request->validate([
            'nama_soal' => 'required|string|max:255',
            'soal_pdf' => 'required|file|mimes:pdf|max:10240',
            'bobot_nilai' => 'required|numeric|min:0',
        ]);

        $path = $request->file('soal_pdf')->store('soal_pdfs', 'public');

        Soal::create([
            'ujian_id' => $exam->ujian_id,
            'nama_soal' => $request->nama_soal,
            'soal_pdf' => $path,
            'bobot_nilai' => $request->bobot_nilai,
        ]);

        return redirect()->route('admin.ujian.detail', $examId)->with('success', 'Soal berhasil ditambahkan.');
    }

    public function updateSoal(Request $request, $examId, $soalId)
    {
        $soal = Soal::findOrFail($soalId);

        $request->validate([
            'nama_soal' => 'required|string|max:255',
            'soal_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'bobot_nilai' => 'required|numeric|min:0',
        ]);

        $soal->nama_soal = $request->nama_soal;
        $soal->bobot_nilai = $request->bobot_nilai;

        if ($request->hasFile('soal_pdf')) {
            // Delete old PDF
            if ($soal->soal_pdf) {
                Storage::disk('public')->delete($soal->soal_pdf);
            }
            $soal->soal_pdf = $request->file('soal_pdf')->store('soal_pdfs', 'public');
        }

        $soal->save();

        return redirect()->route('admin.ujian.detail', $examId)->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroySoal($examId, $soalId)
    {
        $soal = Soal::findOrFail($soalId);
        if ($soal->soal_pdf) {
            Storage::disk('public')->delete($soal->soal_pdf);
        }
        $soal->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus.');
    }

    // Detail soal & test cases
    public function soalDetail($examId, $soalId)
    {
        $exam = Ujian::findOrFail($examId);
        $question = Soal::with('testCases')->findOrFail($soalId);
        $testCases = $question->testCases;

        return view('admin.ujian.soal.detail', [
            'exam' => $exam,
            'question' => $question,
            'testCases' => $testCases,
        ]);
    }

    // ==========================================
    // TEST CASE CRUD
    // ==========================================
    public function storeTestCase(Request $request, $examId, $soalId)
    {
        $soal = Soal::findOrFail($soalId);

        $request->validate([
            'input' => 'required|string',
            'expected_output' => 'required|string',
        ]);

        TestCase::create([
            'soal_id' => $soal->soal_id,
            'input' => $request->input('input'),
            'expected_output' => $request->expected_output,
        ]);

        return redirect()->route('admin.ujian.soal.detail', [$examId, $soalId])->with('success', 'Test case berhasil ditambahkan.');
    }

    public function updateTestCase(Request $request, $examId, $soalId, $tcId)
    {
        $tc = TestCase::findOrFail($tcId);

        $request->validate([
            'input' => 'required|string',
            'expected_output' => 'required|string',
        ]);

        $tc->update([
            'input' => $request->input('input'),
            'expected_output' => $request->expected_output,
        ]);

        return redirect()->route('admin.ujian.soal.detail', [$examId, $soalId])->with('success', 'Test case berhasil diperbarui.');
    }

    public function destroyTestCase($examId, $soalId, $tcId)
    {
        $tc = TestCase::findOrFail($tcId);
        $tc->delete();

        return redirect()->back()->with('success', 'Test case berhasil dihapus.');
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================
    public function users()
    {
        $admins = User::where('role', 'Admin')->get();
        $pengawas = User::where('role', 'Pengawas')->get();
        $mahasiswa = User::where('role', 'Mahasiswa')->get();

        return view('admin.user.index', [
            'admins' => $admins,
            'pengawas' => $pengawas,
            'mahasiswa' => $mahasiswa,
        ]);
    }

    public function storeUser(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Pengawas,Mahasiswa',
            'password' => 'required|string|min:8',
        ];

        if ($request->role === 'Mahasiswa') {
            $rules['nim_nip'] = ['required', 'string', 'size:8', 'regex:/^[A-Za-z]/', 'unique:users,nim_nip'];
        } else {
            $rules['nim_nip'] = 'required|string|unique:users,nim_nip';
        }

        $request->validate($rules, [
            'nim_nip.size' => 'NIM harus tepat 8 karakter.',
            'nim_nip.regex' => 'Karakter pertama NIM harus berupa huruf.',
            'nim_nip.unique' => 'NIM/NIP sudah terdaftar.',
        ]);

        User::create([
            'name' => $request->name,
            'nim_nip' => $request->nim_nip,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Pengawas,Mahasiswa',
        ];

        if ($request->role === 'Mahasiswa') {
            $rules['nim_nip'] = ['required', 'string', 'size:8', 'regex:/^[A-Za-z]/', 'unique:users,nim_nip,'.$id.',user_id'];
        } else {
            $rules['nim_nip'] = 'required|string|unique:users,nim_nip,'.$id.',user_id';
        }

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
        }

        $request->validate($rules, [
            'nim_nip.size' => 'NIM harus tepat 8 karakter.',
            'nim_nip.regex' => 'Karakter pertama NIM harus berupa huruf.',
            'nim_nip.unique' => 'NIM/NIP sudah terdaftar.',
        ]);

        $user->name = $request->name;
        $user->nim_nip = $request->nim_nip;
        $user->role = $request->role;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        if ($id == \Illuminate\Support\Facades\Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        $tab = strtolower($user->role);
        $user->delete();
        
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil dihapus.');
    }
}
