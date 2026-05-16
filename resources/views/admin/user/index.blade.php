@extends('layouts.sidebar')

@section('title', 'Manajemen User')
@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.dashboard') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-speedometer2 sidebar-icon"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('admin.ujian.index') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Manajemen Ujian</span>
    </a>
    <a href="{{ route('admin.users') }}"
        class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.users') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-people sidebar-icon"></i> <span class="sidebar-text">Manajemen User</span>
    </a>
@endsection

@section('content')
    <style>
        /* Animasi Slide & Fade Direction-Aware untuk Konten Tabel User */
        #userTabsContent {
            overflow-x: hidden;
        }
        /* Default / Slide Right (Navigasi slide ke Kanan -> Tabel juga slide ke Kanan) */
        #userTabsContent.slide-right .tab-pane.fade {
            transition: opacity 0.05s ease; /* Transisi keluar super cepat agar tidak ada delay/gap di Bootstrap */
            transform: translateX(-30px); /* Mulai dari kiri agar gerakannya meluncur ke kanan */
            opacity: 0;
        }
        #userTabsContent.slide-right .tab-pane.fade.show {
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            transform: translateX(0);
            opacity: 1;
        }

        /* Slide Left (Navigasi slide ke Kiri -> Tabel juga slide ke Kiri) */
        #userTabsContent.slide-left .tab-pane.fade {
            transition: opacity 0.05s ease; /* Transisi keluar super cepat */
            transform: translateX(30px); /* Mulai dari kanan agar gerakannya meluncur ke kiri */
            opacity: 0;
        }
        #userTabsContent.slide-left .tab-pane.fade.show {
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            transform: translateX(0);
            opacity: 1;
        }

        /* Animasi Sliding Pill Indicator untuk Navigasi Tab */
        #userTabs {
            position: relative;
            z-index: 1;
        }
        #userTabs .nav-link {
            transition: color 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            color: var(--bs-dark);
            background-color: transparent !important; /* Mencegah background bawaan bootstrap muncul mendadak */
            position: relative;
            z-index: 2;
        }
        #userTabs .nav-link.active {
            color: #ffffff !important;
        }
        #tabIndicator {
            position: absolute;
            top: 0.5rem;
            bottom: 0.5rem;
            left: 0.5rem; /* Posisi awal default */
            width: 0;
            background-color: var(--bs-primary);
            border-radius: 50rem;
            transition: left 0.28s cubic-bezier(0.16, 1, 0.3, 1), width 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1;
        }
    </style>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Manajemen User</h4>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Terjadi Kesalahan:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm d-inline-flex position-relative" id="userTabs" role="tablist">
            <div id="tabIndicator"></div>
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="admin-tab" data-bs-toggle="tab"
                    data-bs-target="#admin-pane" type="button" role="tab" aria-controls="admin-pane"
                    aria-selected="true">
                    <i class="bi bi-person-badge me-1"></i> Admin
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pengawas-tab" data-bs-toggle="tab"
                    data-bs-target="#pengawas-pane" type="button" role="tab" aria-controls="pengawas-pane"
                    aria-selected="false">
                    <i class="bi bi-person-video2 me-1"></i> Pengawas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="mahasiswa-tab" data-bs-toggle="tab"
                    data-bs-target="#mahasiswa-pane" type="button" role="tab" aria-controls="mahasiswa-pane"
                    aria-selected="false">
                    <i class="bi bi-mortarboard me-1"></i> Mahasiswa
                </button>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content slide-right" id="userTabsContent">

            <!-- Admin Pane -->
            <div class="tab-pane fade show active" id="admin-pane" role="tabpanel" aria-labelledby="admin-tab"
                tabindex="0">
                <!-- Administrators Table -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-badge text-primary me-2"></i> Admin</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchAdminInput" class="form-control form-control-sm search-input"
                                placeholder="Cari Admin (NIP/Nama)...">
                            <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal"
                                data-bs-target="#addAdminModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Admin
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-top">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th style="width: 25%;">NIP</th>
                                        <th style="width: 55%;">Nama</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="adminTableBody">
                                    @forelse($admins as $u)
                                        <tr class="admin-row" data-nim="{{ $u->nim_nip }}"
                                            data-nama="{{ $u->name }}">
                                            <td>{{ $u->nim_nip }}</td>
                                            <td class="fw-semibold">{{ $u->name }}</td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal{{ $u->user_id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                @if ($u->user_id != Auth::id())
                                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                        data-bs-target="#deleteUserModal{{ $u->user_id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Edit User Modal -->
                                        <div class="modal fade" id="editUserModal{{ $u->user_id }}" tabindex="-1"
                                            aria-labelledby="editUserModalLabel{{ $u->user_id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Admin</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.update', $u->user_id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <input type="hidden" name="role" value="Admin">
                                                            <div class="mb-3"><label
                                                                    class="form-label">NIP</label><input type="text"
                                                                    name="nim_nip" class="form-control"
                                                                    value="{{ $u->nim_nip }}" required></div>
                                                            <div class="mb-3"><label
                                                                    class="form-label">Nama</label><input type="text"
                                                                    name="name" class="form-control"
                                                                    value="{{ $u->name }}" required></div>
                                                            <div class="mb-3"><label class="form-label">Password Baru
                                                                    (Opsional)
                                                                </label><input type="password" name="password"
                                                                    class="form-control" minlength="8">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Confirmation -->
                                        <div class="modal fade" id="deleteUserModal{{ $u->user_id }}" tabindex="-1"
                                            aria-labelledby="deleteUserModalLabel{{ $u->user_id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Admin?</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.destroy', $u->user_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body">Hapus admin
                                                            <strong>{{ $u->name }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-start">Tidak ada admin.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengawas Pane -->
            <div class="tab-pane fade" id="pengawas-pane" role="tabpanel" aria-labelledby="pengawas-tab"
                tabindex="0">
                <!-- Pengawas Table -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-video2 text-warning me-2"></i> Pengawas</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchPengawasInput"
                                class="form-control form-control-sm search-input"
                                placeholder="Cari Pengawas (NIP/Nama)...">
                            <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal"
                                data-bs-target="#addPengawasModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Pengawas
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-top">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th style="width: 25%;">NIP</th>
                                        <th style="width: 55%;">Nama</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="pengawasTableBody">
                                    @forelse($pengawas as $u)
                                        <tr class="pengawas-row" data-nim="{{ $u->nim_nip }}"
                                            data-nama="{{ $u->name }}">
                                            <td>{{ $u->nim_nip }}</td>
                                            <td class="fw-semibold">{{ $u->name }}</td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editPengawasModal{{ $u->user_id }}"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deletePengawasModal{{ $u->user_id }}"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>

                                        <!-- Edit Pengawas Modal -->
                                        <div class="modal fade" id="editPengawasModal{{ $u->user_id }}" tabindex="-1"
                                            aria-labelledby="editPengawasModalLabel{{ $u->user_id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Pengawas</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.update', $u->user_id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <input type="hidden" name="role" value="Pengawas">
                                                            <div class="mb-3"><label
                                                                    class="form-label">NIP</label><input type="text"
                                                                    name="nim_nip" class="form-control"
                                                                    value="{{ $u->nim_nip }}" required></div>
                                                            <div class="mb-3"><label
                                                                    class="form-label">Nama</label><input type="text"
                                                                    name="name" class="form-control"
                                                                    value="{{ $u->name }}" required></div>
                                                            <div class="mb-3"><label class="form-label">Password Baru
                                                                    (Opsional)
                                                                </label><input type="password" name="password"
                                                                    class="form-control" minlength="8">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Pengawas Modal -->
                                        <div class="modal fade" id="deletePengawasModal{{ $u->user_id }}"
                                            tabindex="-1" aria-labelledby="deletePengawasModalLabel{{ $u->user_id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Pengawas?</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.destroy', $u->user_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body">Hapus pengawas
                                                            <strong>{{ $u->name }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-start">Tidak ada pengawas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mahasiswa Pane -->
            <div class="tab-pane fade" id="mahasiswa-pane" role="tabpanel" aria-labelledby="mahasiswa-tab"
                tabindex="0">
                <!-- Mahasiswa Table -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div
                        class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0"><i class="bi bi-mortarboard text-success me-2"></i> Mahasiswa</h5>
                        <div class="d-flex gap-2">
                            <input type="text" id="searchMahasiswaInput"
                                class="form-control form-control-sm search-input"
                                placeholder="Cari Mahasiswa (NIM/Nama)...">
                            <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal"
                                data-bs-target="#addMahasiswaModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Mahasiswa
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-top">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th style="width: 25%;">NIM</th>
                                        <th style="width: 55%;">Nama</th>
                                        <th style="width: 20%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="mahasiswaTableBody">
                                    @forelse($mahasiswa as $u)
                                        <tr class="mahasiswa-row" data-nim="{{ $u->nim_nip }}"
                                            data-nama="{{ $u->name }}">
                                            <td>{{ $u->nim_nip }}</td>
                                            <td class="fw-semibold">{{ $u->name }}</td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                    data-bs-target="#editMahasiswaModal{{ $u->user_id }}"><i
                                                        class="bi bi-pencil"></i></button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteMahasiswaModal{{ $u->user_id }}"><i
                                                        class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>

                                        <!-- Edit Mahasiswa Modal -->
                                        <div class="modal fade" id="editMahasiswaModal{{ $u->user_id }}"
                                            tabindex="-1" aria-labelledby="editMahasiswaModalLabel{{ $u->user_id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Mahasiswa</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.update', $u->user_id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <input type="hidden" name="role" value="Mahasiswa">
                                                            <div class="mb-3"><label
                                                                    class="form-label">NIM</label><input type="text"
                                                                    name="nim_nip" class="form-control"
                                                                    value="{{ $u->nim_nip }}" required></div>
                                                            <div class="mb-3"><label
                                                                    class="form-label">Nama</label><input type="text"
                                                                    name="name" class="form-control"
                                                                    value="{{ $u->name }}" required></div>
                                                            <div class="mb-3"><label class="form-label">Password Baru
                                                                    (Opsional)
                                                                </label><input type="password" name="password"
                                                                    class="form-control" minlength="8">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Mahasiswa Modal -->
                                        <div class="modal fade" id="deleteMahasiswaModal{{ $u->user_id }}"
                                            tabindex="-1" aria-labelledby="deleteMahasiswaModalLabel{{ $u->user_id }}"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Hapus Mahasiswa?</h5>
                                                    </div>
                                                    <form method="POST"
                                                        action="{{ route('admin.users.destroy', $u->user_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body">Hapus mahasiswa
                                                            <strong>{{ $u->name }}</strong>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-start">Tidak ada mahasiswa.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Admin</h5>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="role" value="Admin">
                        <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nim_nip"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                name="password" class="form-control" required minlength="8"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Pengawas Modal -->
    <div class="modal fade" id="addPengawasModal" tabindex="-1" aria-labelledby="addPengawasModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengawas</h5>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="role" value="Pengawas">
                        <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nim_nip"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                name="password" class="form-control" required minlength="8"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Mahasiswa Modal -->
    <div class="modal fade" id="addMahasiswaModal" tabindex="-1" aria-labelledby="addMahasiswaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mahasiswa</h5>
                </div>
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="role" value="Mahasiswa">
                        <div class="mb-3"><label class="form-label">NIM</label><input type="text" name="nim_nip"
                                class="form-control" placeholder="Contoh: D0221001" required></div>
                        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name"
                                class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Password</label><input type="password"
                                name="password" class="form-control" required minlength="8"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ========================
            // TAB ACTIVATION LOGIC
            // ========================
            const urlParams = new URLSearchParams(window.location.search);
            let activeTab = urlParams.get('tab');

            // If no tab in URL, check if there's a validation error with old role
            @if (old('role'))
                if (!activeTab) {
                    activeTab = '{{ strtolower(old('role')) }}';
                }
            @endif

            if (activeTab) {
                const tabMap = {
                    'admin': {
                        tabId: 'admin-tab',
                        paneId: 'admin-pane'
                    },
                    'pengawas': {
                        tabId: 'pengawas-tab',
                        paneId: 'pengawas-pane'
                    },
                    'mahasiswa': {
                        tabId: 'mahasiswa-tab',
                        paneId: 'mahasiswa-pane'
                    }
                };

                const tabInfo = tabMap[activeTab.toLowerCase()];
                if (tabInfo) {
                    document.querySelectorAll('[role="tab"]').forEach(tab => {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                    });

                    document.querySelectorAll('[role="tabpanel"]').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });

                    const tabButton = document.getElementById(tabInfo.tabId);
                    const pane = document.getElementById(tabInfo.paneId);

                    if (tabButton && pane) {
                        tabButton.classList.add('active');
                        tabButton.setAttribute('aria-selected', 'true');
                        pane.classList.add('show', 'active');
                    }
                }
            }

            // ========================
            // SEARCH/FILTER LOGIC
            // ========================
            const searchConfigs = [{
                    inputId: 'searchAdminInput',
                    tableBodyId: 'adminTableBody',
                    rowClass: 'admin-row'
                },
                {
                    inputId: 'searchPengawasInput',
                    tableBodyId: 'pengawasTableBody',
                    rowClass: 'pengawas-row'
                },
                {
                    inputId: 'searchMahasiswaInput',
                    tableBodyId: 'mahasiswaTableBody',
                    rowClass: 'mahasiswa-row'
                }
            ];

            searchConfigs.forEach(config => {
                const searchInput = document.getElementById(config.inputId);
                const tableBody = document.getElementById(config.tableBodyId);

                if (searchInput && tableBody) {
                    searchInput.addEventListener('keyup', function() {
                        const searchTerm = this.value.toLowerCase();
                        const rows = tableBody.querySelectorAll('.' + config.rowClass);

                        rows.forEach(row => {
                            const nim = row.getAttribute('data-nim').toLowerCase();
                            const nama = row.getAttribute('data-nama').toLowerCase();

                            if (nim.includes(searchTerm) || nama.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                }
            });

            // ========================
            // SLIDING PILL INDICATOR LOGIC
            // ========================
            const tabsContainer = document.getElementById('userTabs');
            const indicator = document.getElementById('tabIndicator');

            function updateTabIndicator() {
                const activeTab = document.querySelector('#userTabs .nav-link.active');
                if (activeTab && indicator && tabsContainer) {
                    const containerRect = tabsContainer.getBoundingClientRect();
                    const activeRect = activeTab.getBoundingClientRect();

                    const leftPos = activeRect.left - containerRect.left;
                    const width = activeRect.width;

                    indicator.style.left = leftPos + 'px';
                    indicator.style.width = width + 'px';
                }
            }

            if (tabsContainer && indicator) {
                // Jalankan saat load pertama dan resize
                setTimeout(updateTabIndicator, 50);
                window.addEventListener('resize', updateTabIndicator);

                // Tambahkan listener untuk setiap perubahan tab
                document.querySelectorAll('#userTabs .nav-link').forEach(button => {
                    button.addEventListener('shown.bs.tab', updateTabIndicator);
                    button.addEventListener('click', function() {
                        setTimeout(updateTabIndicator, 10);
                    });
                });
            }

            // ========================
            // DIRECTION-AWARE TABLE SLIDE LOGIC
            // ========================
            const tabButtons = document.querySelectorAll('#userTabs .nav-link');
            const tabsContentContainer = document.getElementById('userTabsContent');
            let currentTabIndex = 0;

            // Cari index tab yang aktif saat pertama kali load
            tabButtons.forEach((btn, idx) => {
                if (btn.classList.contains('active')) {
                    currentTabIndex = idx;
                }
            });

            tabButtons.forEach((button, index) => {
                button.addEventListener('click', function() {
                    if (index > currentTabIndex) {
                        // Navigasi ke kanan -> Tabel masuk dari kanan
                        tabsContentContainer.classList.remove('slide-left');
                        tabsContentContainer.classList.add('slide-right');
                    } else if (index < currentTabIndex) {
                        // Navigasi ke kiri -> Tabel masuk dari kiri
                        tabsContentContainer.classList.remove('slide-right');
                        tabsContentContainer.classList.add('slide-left');
                    }
                    currentTabIndex = index;
                });
            });
        });
    </script>
@endsection
