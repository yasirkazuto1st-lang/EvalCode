@extends('layouts.sidebar')

@section('title', 'Manajemen User')
@section('sidebar-menu')
    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.dashboard') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-speedometer2 sidebar-icon"></i> <span class="sidebar-text">Dashboard</span>
    </a>
    <a href="{{ route('admin.ujian.index') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.ujian.*') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-card-list sidebar-icon"></i> <span class="sidebar-text">Manajemen Soal</span>
    </a>
    <a href="{{ route('admin.users') }}" class="list-group-item list-group-item-action bg-transparent {{ request()->routeIs('admin.users') ? 'active text-nowrap' : 'text-nowrap' }}">
        <i class="bi bi-people sidebar-icon"></i> <span class="sidebar-text">Manajemen User</span>
    </a>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Manajemen User</h4>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm d-inline-flex" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-pane" type="button" role="tab" aria-controls="admin-pane" aria-selected="true">
                <i class="bi bi-person-badge me-1"></i> Admin
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="pengawas-tab" data-bs-toggle="tab" data-bs-target="#pengawas-pane" type="button" role="tab" aria-controls="pengawas-pane" aria-selected="false">
                <i class="bi bi-person-video2 me-1"></i> Pengawas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4" id="mahasiswa-tab" data-bs-toggle="tab" data-bs-target="#mahasiswa-pane" type="button" role="tab" aria-controls="mahasiswa-pane" aria-selected="false">
                <i class="bi bi-mortarboard me-1"></i> Mahasiswa
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="userTabsContent">
        
        <!-- Admin Pane -->
        <div class="tab-pane fade show active" id="admin-pane" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
            <!-- Administrators Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-person-badge text-primary me-2"></i> Admin</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Cari Admin...">
                <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Admin
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead class="table-light"><tr class="text-nowrap"><th style="width: 25%;">NIP</th><th style="width: 55%;">Nama</th><th style="width: 20%;">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($admins as $u)
                        <tr>
                            <td>{{ $u['nim_nip'] }}</td>
                            <td class="fw-semibold">{{ $u['name'] }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u['nim_nip'] }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $u['nim_nip'] }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal{{ $u['nim_nip'] }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $u['nim_nip'] }}" aria-hidden="true">
                          <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Edit Admin</h5></div>
                            <form method="POST" action="{{ route('admin.users.update', $u['user_id']) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" name="role" value="Admin">
                                <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nim_nip" class="form-control" value="{{ $u['nim_nip'] }}" required></div>
                                <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" value="{{ $u['name'] }}" required></div>
                                <div class="mb-3"><label class="form-label">Password Baru (Opsional)</label><input type="password" name="password" class="form-control" minlength="8"></div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                            </form>
                          </div></div>
                        </div>

                        <!-- Delete Confirmation -->
                        <div class="modal fade" id="deleteUserModal{{ $u['nim_nip'] }}" tabindex="-1" aria-labelledby="deleteUserModalLabel{{ $u['nim_nip'] }}" aria-hidden="true">
                          <div class="modal-dialog"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Hapus Admin?</h5></div>
                            <form method="POST" action="{{ route('admin.users.destroy', $u['user_id']) }}">
                            @csrf
                            @method('DELETE')
                            <div class="modal-body">Hapus admin <strong>{{ $u['name'] }}</strong>?</div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-danger">Hapus</button>
                            </div>
                            </form>
                          </div></div>
                        </div>

                        @empty
                        <tr><td colspan="3" class="text-start">Tidak ada admin.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>

        <!-- Pengawas Pane -->
        <div class="tab-pane fade" id="pengawas-pane" role="tabpanel" aria-labelledby="pengawas-tab" tabindex="0">
            <!-- Pengawas Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-person-video2 text-warning me-2"></i> Pengawas</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Cari Pengawas...">
                <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addPengawasModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pengawas
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead class="table-light"><tr class="text-nowrap"><th style="width: 25%;">NIP</th><th style="width: 55%;">Nama</th><th style="width: 20%;">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($pengawas as $u)
                        <tr>
                            <td>{{ $u['nim_nip'] }}</td>
                            <td class="fw-semibold">{{ $u['name'] }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editPengawasModal{{ $u['nim_nip'] }}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deletePengawasModal{{ $u['nim_nip'] }}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-start">Tidak ada pengawas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>

        <!-- Mahasiswa Pane -->
        <div class="tab-pane fade" id="mahasiswa-pane" role="tabpanel" aria-labelledby="mahasiswa-tab" tabindex="0">
            <!-- Mahasiswa Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-mortarboard text-success me-2"></i> Mahasiswa</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Cari Mahasiswa...">
                <button class="btn btn-sm btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addMahasiswaModal">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Mahasiswa
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-top">
                    <thead class="table-light"><tr class="text-nowrap"><th style="width: 25%;">NIM</th><th style="width: 55%;">Nama</th><th style="width: 20%;">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($mahasiswa as $u)
                        <tr>
                            <td>{{ $u['nim_nip'] }}</td>
                            <td class="fw-semibold">{{ $u['name'] }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#editMahasiswaModal{{ $u['nim_nip'] }}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMahasiswaModal{{ $u['nim_nip'] }}"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-start">Tidak ada mahasiswa.</td></tr>
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
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Tambah Admin</h5></div>
    <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="modal-body">
        <input type="hidden" name="role" value="Admin">
        <div class="mb-3"><label class="form-label">NIP</label><input type="text" name="nim_nip" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="8"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
    </form>
  </div></div>
</div>

<!-- Add Pengawas Modal -->
<div class="modal fade" id="addPengawasModal" tabindex="-1" aria-labelledby="addPengawasModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Tambah Pengawas</h5></div>
    <div class="modal-body">
      <form>
        <div class="mb-3"><label class="form-label">NIP</label><input type="text" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Ulangi Password</label><input type="password" class="form-control"></div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </div></div>
</div>

<!-- Add Mahasiswa Modal -->
<div class="modal fade" id="addMahasiswaModal" tabindex="-1" aria-labelledby="addMahasiswaModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Tambah Mahasiswa</h5></div>
    <div class="modal-body">
      <form>
        <div class="mb-3"><label class="form-label">NIM</label><input type="text" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Nama</label><input type="text" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Ulangi Password</label><input type="password" class="form-control"></div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </div></div>
</div>
@endsection
