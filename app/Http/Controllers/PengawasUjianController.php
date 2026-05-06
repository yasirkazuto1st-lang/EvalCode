<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PengawasUjianController extends Controller
{
    /**
     * Show the monitoring detail for an exam.
     *
     * For now we use a dummy empty collection and paginate it.
     * In a real implementation this would query the database
     * for participants of the specific exam.
     */
    public function detail(Request $request)
    {
        // Dummy data for demonstration – replace with real DB query later
        $raw = collect([
            [
                'id' => 1,
                'nim' => 'D0221001',
                'name' => 'Ahmad Fauzi',
                'status_badge' => '4/5 Selesai',
                'similarity' => 85,
                'total_score' => 150,
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
        ]);

        // Convert arrays to stdClass objects for Blade property access
        $participants = $raw->map(function($item){
            $item['submissions'] = collect($item['submissions'])->map(fn($s) => (object) $s);
            return (object) $item;
        });

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $total = $participants->count();
        $results = $participants->forPage($page, $perPage);
        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('pengawas.ujian.detail', ['participants' => $paginator]);
    }

    public function soal()
    {
        return view('pengawas.ujian.soal');
    }

    public function password()
    {
        return view('pengawas.password');
    }
}
?>
