<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = (object) [
            'total_ujian' => 15,
            'total_admin' => 3,
            'total_pengawas' => 12,
            'total_mahasiswa' => 245,
            'active_ujian' => 2,
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function password()
    {
        return view('admin.password');
    }

    // List of exams (dummy data)
    public function exams()
    {
        $exams = collect([
            [
                'id' => 1,
                'title' => 'Ujian Komprehensif Dasar Pemrograman',
                'description' => 'Ujian akhir semester untuk mata kuliah dasar pemrograman.',
                'duration' => 120,
                'passing_grade' => 70,
            ],
            [
                'id' => 2,
                'title' => 'Ujian Algoritma & Struktur Data',
                'description' => 'Ujian tengah semester.',
                'duration' => 90,
                'passing_grade' => 65,
            ],
        ]);

        // paginate (10 per page)
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $total = $exams->count();
        $results = $exams->forPage($page, $perPage);
        $paginator = new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);

        return view('admin.ujian.index', ['exams' => $paginator]);
    }

    // Detail of a specific exam (dummy data)
    public function examDetail($id)
    {
        // Dummy exam data
        $exam = (object) [
            'id' => $id,
            'title' => 'Ujian Contoh #' . $id,
            'description' => 'Deskripsi singkat exam #' . $id,
            'duration' => 120,
            'passing_grade' => 70,
        ];

        // Dummy soal list
        $questions = collect([
            [
                'id' => 1,
                'name' => 'Soal 1 - Hello World',
                'weight' => 20,
            ],
            [
                'id' => 2,
                'name' => 'Soal 2 - Deret Fibonacci',
                'weight' => 30,
            ],
        ]);

        // Dummy participants list
        $participants = collect([
            [
                'id' => 1,
                'nim' => 'D0221001',
                'name' => 'Ahmad Fauzi',
                'status_badge' => '4/5 Selesai',
                'similarity' => 85,
                'total_score' => 150,
                'status_lulus' => '<span class="badge bg-success">Lulus</span>',
                'submissions' => [
                    [
                        'time' => '10:15:32',
                        'question' => '2. Deret Fibonacci',
                        'status_badge' => '<span class="badge bg-success">Accepted</span>',
                        'score' => 30,
                        'similarity' => 85,
                    ],
                    [
                        'time' => '09:45:10',
                        'question' => '1. Hello World',
                        'status_badge' => '<span class="badge bg-success">Accepted</span>',
                        'score' => 20,
                        'similarity' => 15,
                    ],
                ],
            ],
            [
                'id' => 2,
                'nim' => 'D0221002',
                'name' => 'Budi Santoso',
                'status_badge' => '2/5 Selesai',
                'similarity' => 12,
                'total_score' => 20,
                'status_lulus' => '<span class="badge bg-danger">Tidak Lulus</span>',
                'submissions' => [
                    [
                        'time' => '10:30:22',
                        'question' => '1. Hello World',
                        'status_badge' => '<span class="badge bg-success">Accepted</span>',
                        'score' => 20,
                        'similarity' => 12,
                    ],
                    [
                        'time' => '10:12:05',
                        'question' => '1. Hello World',
                        'status_badge' => '<span class="badge bg-danger">Wrong Answer</span>',
                        'score' => 0,
                        'similarity' => 8,
                    ],
                ],
            ],
        ])->map(function($item){
            $item['submissions'] = collect($item['submissions'])->map(fn($s) => (object) $s);
            return (object) $item;
        });

        return view('admin.ujian.detail', [
            'exam' => $exam,
            'questions' => $questions,
            'participants' => $participants,
        ]);
    }

    // Detail of a specific soal (question) and its test cases
    public function soalDetail($examId, $soalId)
    {
        $question = (object) [
            'id' => $soalId,
            'name' => 'Soal ' . $soalId . ' - Contoh',
            'pdf_url' => '#', // placeholder
        ];

        $testCases = collect([
            [
                'id' => 1,
                'input' => '1 2 3',
                'expected_output' => '6',
            ],
            [
                'id' => 2,
                'input' => '4 5',
                'expected_output' => '9',
            ],
        ]);

        return view('admin.ujian.soal.detail', [
            'question' => $question,
            'testCases' => $testCases,
        ]);
    }

    public function users()
    {
        $admins = \App\Models\User::where('role', 'Admin')->get();
        $pengawas = \App\Models\User::where('role', 'Pengawas')->get();
        $mahasiswa = \App\Models\User::where('role', 'Mahasiswa')->get();

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

        \App\Models\User::create([
            'name' => $request->name,
            'nim_nip' => $request->nim_nip,
            'role' => $request->role,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

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
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
        $user->save();

        $tab = strtolower($request->role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil diperbarui.');
    }

    public function destroyUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $role = $user->role;
        $user->delete();
        
        $tab = strtolower($role);
        return redirect()->route('admin.users', ['tab' => $tab])->with('success', 'User berhasil dihapus.');
    }
}
?>
