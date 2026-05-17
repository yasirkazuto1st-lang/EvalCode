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
    /**
     * Tampilkan halaman dashboard Admin beserta statistik ringkas.
     * 
     * @return \Illuminate\View\View
     */
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

    /**
     * Tampilkan halaman ganti password untuk Admin.
     * 
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('admin.password');
    }

    // ==========================================
    // UJIAN CRUD
    // ==========================================
    /**
     * Tampilkan daftar seluruh Ujian yang dikelola.
     * 
     * @return \Illuminate\View\View
     */
    public function exams()
    {
        $exams = Ujian::orderBy('created_at', 'desc')->get();
        return view('admin.ujian.index', ['exams' => $exams]);
    }

    /**
     * Proses penyimpanan data Ujian baru ke database.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeExam(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:0',
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

    /**
     * Proses pembaruan data Ujian yang sudah ada.
     * 
     * @param Request $request
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateExam(Request $request, $id)
    {
        $exam = Ujian::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'durasi' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:0',
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

    /**
     * Hapus data Ujian secara permanen beserta file PDF soal yang terlampir.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Tampilkan detail Ujian, daftar soal, serta rekapitulasi nilai peserta (Mahasiswa).
     * Melakukan kalkulasi nilai akhir secara dinamis berdasarkan skor tertinggi tiap soal.
     * 
     * @param int $id ID Ujian
     * @return \Illuminate\View\View
     */
    public function examDetail($id)
    {
        $exam = Ujian::with('soals')->findOrFail($id);
        $questions = $exam->soals;

        // ==========================================
        // KALKULASI REKAPITULASI PESERTA
        // ==========================================
        // 1. Ambil skor maksimal untuk setiap soal yang dikerjakan oleh tiap peserta.
        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select(
                'submissions.user_id', 
                'submissions.soal_id', 
                \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'), 
                \Illuminate\Support\Facades\DB::raw("MAX(CASE WHEN submissions.status = 'Accepted' THEN 1 ELSE 0 END) as is_accepted")
            )
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        // 2. Gabungkan skor maksimal tiap soal menjadi total skor per peserta.
        $query = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select(
                'users.name', 
                'users.nim_username', 
                'users.user_id', 
                \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'), 
                \Illuminate\Support\Facades\DB::raw('SUM(sub.is_accepted) as accepted_count')
            );

        $participants = $query->groupBy('users.name', 'users.nim_username', 'users.user_id')
            ->orderByDesc('total_skor')
            ->get();

        $totalSoal = $exam->soals->count();
        $maxScore = $exam->soals->sum('bobot_nilai');
        $passingGrade = $exam->passing_grade;

        // Ambil seluruh riwayat submission untuk ditampilkan di tabel dropdown riwayat
        $allSubmissions = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.*', 'soals.nama_soal', 'soals.bobot_nilai')
            ->orderByDesc('submissions.created_at')
            ->get()
            ->groupBy('user_id');

        // Tambahkan atribut persentase progres dan status kelulusan pada tiap peserta
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

    /**
     * Tampilkan riwayat lengkap submisi seorang peserta pada suatu ujian.
     * 
     * @param int $examId ID Ujian
     * @param int $userId ID User (Mahasiswa)
     * @return \Illuminate\View\View
     */
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

    /**
     * Ekspor laporan hasil ujian (peserta dan statistik soal) ke file Excel (.xlsx).
     * 
     * @param int $id ID Ujian
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel($id)
    {
        $exam = Ujian::findOrFail($id);

        // ==========================================
        // 1. DATA PESERTA (Tabel Kiri)
        // ==========================================
        $subQuery = \Illuminate\Support\Facades\DB::table('submissions')
            ->join('soals', 'submissions.soal_id', '=', 'soals.soal_id')
            ->where('soals.ujian_id', $exam->ujian_id)
            ->select('submissions.user_id', \Illuminate\Support\Facades\DB::raw('MAX(submissions.skor) as max_skor'))
            ->groupBy('submissions.user_id', 'submissions.soal_id');

        $participants = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->join('users', 'sub.user_id', '=', 'users.user_id')
            ->select('users.name', 'users.nim_username', \Illuminate\Support\Facades\DB::raw('SUM(sub.max_skor) as total_skor'))
            ->where('users.role', 'mahasiswa')
            ->groupBy('users.name', 'users.nim_username', 'users.user_id')
            ->orderByDesc('total_skor')
            ->get();

        $maxScore = $exam->soals->sum('bobot_nilai');
        $passingGrade = $exam->passing_grade;

        $exportData = [];
        foreach ($participants as $p) {
            $scorePercentage = $maxScore > 0 ? ($p->total_skor / $maxScore) * 100 : 0;
            $status = ($p->total_skor ?? 0) >= $passingGrade ? 'Lulus' : 'Tidak Lulus';
            
            $exportData[] = [
                'NIM' => $p->nim_username,
                'Nama Mahasiswa' => $p->name,
                'Status' => $status,
                'Skor' => $p->total_skor ?? 0
            ];
        }

        // ==========================================
        // 2. DATA STATISTIK SOAL (Tabel Kanan)
        // ==========================================
        $soalStats = \Illuminate\Support\Facades\DB::table('submissions')
            ->where('status', 'Accepted')
            ->select('soal_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT user_id) as success_count'))
            ->groupBy('soal_id')
            ->get()
            ->keyBy('soal_id');

        $totalParticipants = $participants->count();
        $soalExportData = [];

        foreach ($exam->soals as $index => $soal) {
            $stat = $soalStats->get($soal->soal_id);
            $successCount = $stat ? $stat->success_count : 0;
            $successPercentage = $totalParticipants > 0 ? ($successCount / $totalParticipants) * 100 : 0;
            $mockPercent = round($successPercentage);

            if ($totalParticipants == 0) {
                $level = 'Belum Dikerjakan';
            } elseif ($mockPercent <= 25) {
                $level = 'Sangat Sulit';
            } elseif ($mockPercent <= 50) {
                $level = 'Sulit';
            } elseif ($mockPercent <= 75) {
                $level = 'Normal';
            } else {
                $level = 'Gampang';
            }

            $soalExportData[] = [
                'no_soal' => $index + 1,
                'nama_soal' => $soal->nama_soal,
                'selesai' => $successCount . ' Orang',
                'kategori' => $level,
            ];
        }

        $examInfo = [
            'judul' => $exam->judul,
            'durasi' => $exam->durasi . " Menit",
            'passing_grade' => $exam->passing_grade . " Pts",
            'tanggal' => date('d-m-Y H:i')
        ];

        $filename = "Laporan_Ujian_{$exam->ujian_id}_" . date('Ymd_His') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UjianExport($exportData, $examInfo, $soalExportData), $filename);
    }

    /**
     * Override skor pada sebuah submisi peserta oleh Admin.
     * 
     * @param Request $request
     * @param int $submissionId ID Submisi
     * @return \Illuminate\Http\RedirectResponse
     */
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
    /**
     * Tambah soal baru beserta file PDF deskripsi soal ke dalam ujian.
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeSoal(Request $request, $examId)
    {
        $exam = Ujian::findOrFail($examId);

        $request->validate([
            'nama_soal' => 'required|string|max:255',
            'soal_pdf' => 'required|file|mimes:pdf|max:10240',
            'bobot_nilai' => 'required|integer|min:0',
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

    /**
     * Perbarui data soal yang ada, termasuk menangani pergantian file PDF jika ada.
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSoal(Request $request, $examId, $soalId)
    {
        $soal = Soal::findOrFail($soalId);

        $request->validate([
            'nama_soal' => 'required|string|max:255',
            'soal_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'bobot_nilai' => 'required|integer|min:0',
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

        // Perbarui skor pada seluruh submisi masa lalu yang berstatus 'Accepted' untuk soal ini
        \Illuminate\Support\Facades\DB::table('submissions')
            ->where('soal_id', $soalId)
            ->where('status', 'Accepted')
            ->update(['skor' => $request->bobot_nilai]);

        return redirect()->route('admin.ujian.detail', $examId)->with('success', 'Soal berhasil diperbarui.');
    }

    /**
     * Hapus soal secara permanen, sekaligus menghapus file PDF fisiknya dari storage.
     * 
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroySoal($examId, $soalId)
    {
        $soal = Soal::findOrFail($soalId);
        if ($soal->soal_pdf) {
            Storage::disk('public')->delete($soal->soal_pdf);
        }
        $soal->delete();

        return redirect()->back()->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Tampilkan detail sebuah soal beserta seluruh test case (input/output) miliknya.
     * 
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\View\View
     */
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
    /**
     * Tambahkan sebuah Test Case (input dan expected output) pada soal.
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Perbarui Test Case yang sudah ada.
     * 
     * @param Request $request
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @param int $tcId ID Test Case
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Hapus sebuah Test Case secara permanen.
     * 
     * @param int $examId ID Ujian
     * @param int $soalId ID Soal
     * @param int $tcId ID Test Case
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTestCase($examId, $soalId, $tcId)
    {
        $tc = TestCase::findOrFail($tcId);
        $tc->delete();

        return redirect()->back()->with('success', 'Test case berhasil dihapus.');
    }

    // ==========================================
    // USER MANAGEMENT
    // ==========================================
    /**
     * Tampilkan halaman manajemen master data User (Mahasiswa, Pengawas, Admin).
     * 
     * @return \Illuminate\View\View
     */
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

    /**
     * Buat User baru (dengan pengecekan format NIM khusus untuk Mahasiswa).
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Pengawas,Mahasiswa',
            'password' => 'required|string|min:8',
        ];

        if ($request->role === 'Mahasiswa') {
            $rules['nim_username'] = ['required', 'string', 'size:8', 'regex:/^[A-Za-z]/', 'unique:users,nim_username'];
        } else {
            $rules['nim_username'] = 'required|string|unique:users,nim_username';
        }

        $request->validate($rules, [
            'nim_username.size' => 'NIM harus tepat 8 karakter.',
            'nim_username.regex' => 'Karakter pertama NIM harus berupa huruf.',
            'nim_username.unique' => $request->role === 'Mahasiswa' ? 'NIM sudah terdaftar.' : 'Username sudah terdaftar.',
        ]);

        User::create([
            'name' => $request->name,
            'nim_username' => $request->nim_username,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Perbarui data User yang sudah ada (termasuk ganti password opsional).
     * 
     * @param Request $request
     * @param int $id ID User
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|in:Admin,Pengawas,Mahasiswa',
        ];

        if ($request->role === 'Mahasiswa') {
            $rules['nim_username'] = ['required', 'string', 'size:8', 'regex:/^[A-Za-z]/', 'unique:users,nim_username,'.$id.',user_id'];
        } else {
            $rules['nim_username'] = 'required|string|unique:users,nim_username,'.$id.',user_id';
        }

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8';
        }

        $request->validate($rules, [
            'nim_username.size' => 'NIM harus tepat 8 karakter.',
            'nim_username.regex' => 'Karakter pertama NIM harus berupa huruf.',
            'nim_username.unique' => $request->role === 'Mahasiswa' ? 'NIM sudah terdaftar.' : 'Username sudah terdaftar.',
        ]);

        $user->name = $request->name;
        $user->nim_username = $request->nim_username;
        $user->role = $request->role;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Hapus akun User secara permanen (Admin tidak bisa menghapus akunnya sendiri).
     * 
     * @param int $id ID User
     * @return \Illuminate\Http\RedirectResponse
     */
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
