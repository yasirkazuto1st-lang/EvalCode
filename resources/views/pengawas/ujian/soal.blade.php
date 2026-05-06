@extends('layouts.sidebar')

@section('title', 'Detail Soal & Testcase')

@section('sidebar-menu')
    <a href="{{ route('pengawas.dashboard') }}" class="list-group-item list-group-item-action bg-transparent active">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Daftar Ujian</span>
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <a href="{{ route('pengawas.ujian.detail') }}" class="btn btn-sm btn-back mb-3">
        <i class="bi bi-arrow-left"></i>
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h3 class="fw-bold text-unsulbar mb-1">1. Hello World & Basic I/O</h3>
            <p class="text-muted mb-0">Ujian Komprehensif Dasar Pemrograman | Bobot: 20</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: PDF Soal -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Dokumen Soal (PDF)</h5>
                </div>
                <div class="card-body">
                    <div class="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted" style="min-height: 500px;">
                        <i class="bi bi-file-pdf fs-1 mb-2"></i>
                        <p class="mb-0">PDF Viewer Placeholder</p>
                        <small>soal_helloworld.pdf</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Test Cases -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-braces text-success me-2"></i> Daftar Test Case</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered font-monospace small">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="45%">Input (stdin)</th>
                                    <th width="45%">Expected Output (stdout)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center align-middle">1</td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">5</pre>
                                    </td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">Hello World
Input Anda: 5</pre>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">2</td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">EvalCode</pre>
                                    </td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">Hello World
Input Anda: EvalCode</pre>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center align-middle">3</td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">Test</pre>
                                    </td>
                                    <td>
                                        <pre class="mb-0 p-2 bg-light rounded border">Hello World
Input Anda: Test</pre>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
