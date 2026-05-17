# EvalCode - Panduan Pengembangan & Dokumentasi Proyek

Dokumen ini berfungsi sebagai panduan referensi sentral untuk memudahkan para _developer_, _maintenance engineer_, atau AI _assistant_ di masa depan untuk memahami arsitektur, teknologi, dan alur kerja utama dalam proyek **EvalCode**.

## 1. Project Overview (Gambaran Umum)

**EvalCode** adalah platform ujian _coding_ (_competitive programming/assessment_) yang memungkinkan dosen atau panitia untuk menyelenggarakan ujian berbasis pemrograman, dimana mahasiswa dapat mengetikkan kode (_source code_) lalu dievaluasi secara otomatis (melalui sistem _autograder_).

**Target Pengguna**:

1. **Admin**: Mengelola data ujian, soal, test case, serta akun pengguna.
2. **Pengawas**: Memonitor jalannya ujian, mengontrol status ujian (mulai/jeda/selesai), serta meninjau hasil nilai.
3. **Mahasiswa**: Peserta yang masuk ke ujian menggunakan token _real-time_ dan mengerjakan soal pemrograman dengan _compiler_ di dalam _browser_.

## 2. Tech Stack (Teknologi Utama)

- **Backend Framework**: Laravel 13 (PHP)
- **Frontend**: Blade Templating Engine + Bootstrap 5 (Styling) + React (sedikit implementasi khusus pada bagian Workspace)
- **Database**: MySQL (relasional)
- **Code Editor**: Monaco Editor (untuk _syntax highlighting_ di halaman pengerjaan)
- **Autograder API**: Judge0 (berjalan di background untuk mengeksekusi kode berbagai bahasa pemrograman seperti **Python, Java, C++, dan Dart**)
- **Exporting**: Laravel Excel / PhpSpreadsheet (ekspor laporan XLSX).

## 3. Architecture & Core Workflow (Arsitektur & Alur Kerja)

### A. Alur Ujian (Role Mahasiswa)

1. Mahasiswa login dan masuk ke **Dashboard**.
2. Mahasiswa melihat daftar ujian aktif dan mengklik tombol "Masuk Ujian".
3. Mahasiswa harus memasukkan **Token** ujian yang valid (didapatkan dari Pengawas melalui proyektor/layar pengawas).
4. Jika valid, Mahasiswa masuk ke halaman **Detail Ujian**, melihat leaderboard, dan daftar soal.
5. Mahasiswa memilih soal, lalu membuka **Workspace**. Di sini Mahasiswa mengetik kode, melakukan _Run/Compile_ (ke Judge0), lalu menekan tombol _Submit_.
6. Submisi akan dievaluasi terhadap seluruh **Test Case** di latar belakang. Skor dihitung otomatis berdasarkan persentase _Test Case_ yang benar (_Accepted_).

### B. Alur Monitoring & Kontrol (Role Pengawas)

1. Pengawas masuk ke menu **Monitoring Ujian**.
2. Pengawas memilih ujian tertentu dan dapat mengubah status: **Start** (mulai), **Pause** (jeda), **Finish** (selesai).
3. Ketika status ujian adalah _Active_, sistem akan **menerbitkan Token Ujian (6 karakter acak)** yang diperbarui secara _real-time_ (AJAX polling setiap 60 detik).
4. Pengawas dapat melihat nilai (_real-time leaderboard_) dan dapat melakukan **Override Skor** (memberikan nilai manual/kompensasi beserta catatan).

### C. Alur Master Data (Role Admin)

1. Admin mengelola (CRUD) master **Ujian**.
2. Di dalam setiap Ujian, Admin mengelola (CRUD) **Soal** (berupa lampiran file PDF dan _bobot nilai_).
3. Di dalam setiap Soal, Admin mengelola (CRUD) **Test Case** (berupa _Input_ dan _Expected Output_).
4. Admin dapat mengunduh **Laporan Excel** (_stacked layout_: Tabel Peserta + Tabel Statistik Soal).
5. Admin dapat mengelola akun **User**.

## 4. Database Schema Overview (Skema Database)

Sistem menggunakan tipe penilaian berbasis **integer murni**.

- `users`: Tabel akun (`user_id`, `name`, `nim_username`, `role`, `password`, `last_session_id`).
- `ujians`: Tabel ujian (`ujian_id`, `judul`, `durasi`, `status`, `passing_grade` (integer)).
- `soals`: Tabel soal (`soal_id`, `ujian_id`, `nama_soal`, `soal_pdf`, `bobot_nilai` (integer)).
- `test_cases`: Tabel skenario tes (`test_case_id`, `soal_id`, `input`, `expected_output`).
- `tokens`: Tabel token _real-time_ (`token_id`, `ujian_id`, `kode_token`, `status_aktif`).
- `submissions`: Tabel rekam jejak jawaban Mahasiswa (`submission_id`, `user_id`, `soal_id`, `source_code`, `language_id`, `status` (String seperti "Accepted", "Wrong Answer"), `skor` (integer), `execution_time`, `memory`, `similarity_index` (untuk deteksi plagiarisme)).

## 5. Directory Structure (Struktur Direktori Kunci)

- `app/Http/Controllers/`:
    - `AdminController.php`
    - `PengawasUjianController.php`
    - `MahasiswaUjianController.php`
    - `AuthController.php`
- `app/Models/`: `Ujian.php`, `Soal.php`, `TestCase.php`, `User.php`, `Token.php`
- `app/Exports/`: `UjianExport.php` (Pengaturan ekspor _layout_ dua tabel)
- `routes/web.php`: Manajemen _routing_ berdasarkan _middleware_ peran (`Admin`, `Pengawas`, `Mahasiswa`).
- `resources/views/`:
    - `admin/`: View untuk master data.
    - `pengawas/`: View untuk monitoring ujian dan token generator.
    - `mahasiswa/`: View untuk _workspace code editor_.

## 6. Fitur Teknis Lanjutan

- **Integrasi Judge0 API**: File `MahasiswaUjianController.php` (metode `submitCode`) menangani komunikasi sinkronisasi _compile_ dan _run_ ke server Judge0. Respons dari Judge0 dikonversi (melalui metode `resolveJudge0StatusForEvalcode`) agar ramah pengguna (contoh: memisahkan _Compilation Error_ dengan _Runtime Error_).
- **Deteksi Plagiarisme Otomatis**: EvalCode menggunakan algoritma **Jaccard Similarity** pada _source code_ setiap submisi (di _MahasiswaUjianController_ metode `submitCode`). Kode baru dibandingkan dengan seluruh kode _Accepted_ dari _user_ lain pada ujian yang sama.
- **Single-Session Enforcement**: Setiap akun hanya boleh login pada satu _device/browser_. Fitur ini diatur pada `AuthController.php` menggunakan pelacakan `$user->last_session_id`.

## 7. Panduan Warna dan Label (UI Indicators)

Sistem menggunakan berbagai warna (_badge_) dan label untuk memudahkan identifikasi status di dalam tabel dan halaman _monitoring_:

### A. Status Ujian

- **Active** (Aktif): Ujian sedang berlangsung. Biasanya diwakili dengan UI _highlight_ warna hijau (_Success_).
- **Closed** (Ditutup sementara): Ujian sedang di-pause oleh pengawas. Diwakili dengan warna kuning (_Warning_).
- **Finished** (Selesai): Ujian telah berakhir secara permanen. Diwakili dengan warna biru (_Primary_).

### B. Indeks Similaritas (Plagiarisme)

Indeks kesamaan (_similarity_) dikategorikan dalam 3 rentang warna untuk membantu Pengawas mengidentifikasi kecurangan:

- **>= 70%** (Merah / _Danger_): Indikasi plagiarisme sangat tinggi.
- **40% - 69%** (Kuning / _Warning_): Indikasi plagiarisme sedang (perlu peninjauan manual).
- **< 40%** (Hijau / _Success_): Kode dianggap aman atau kemiripan wajar.

### C. Tingkat Kesulitan Soal (Berdasarkan Kelulusan)

Diukur berdasarkan persentase _user_ yang berhasil menyelesaikan soal tersebut (_Accepted_) berbanding dengan total peserta ujian.

- **0% (Belum ada)**: Kategori `Belum Dikerjakan` (Abu-abu / _Secondary_).
- **<= 25%**: Kategori `Sangat Sulit` (Merah / _Danger_).
- **26% - 50%**: Kategori `Sulit` (Kuning / _Warning_).
- **51% - 75%**: Kategori `Normal` (Biru / _Primary_).
- **> 75%**: Kategori `Gampang` (Hijau / _Success_).

### D. Status Submisi Kode (Evaluasi Judge0)

- **Accepted** (Hijau / _Success_): Kode berjalan sempurna dan melewati semua _Test Case_.
- **Wrong Answer** (Merah / _Danger_): Kode berjalan, tetapi _output_ tidak sesuai dengan _Expected Output_.
- **Time Limit Exceeded** (Kuning / _Warning_): Kode berjalan terlalu lama melebihi batas waktu maksimal (_timeout_).
- **Compilation Error** (Abu-abu / _Secondary_): Kode gagal di-compile (terjadi _syntax error_ pada C++/Java, dll).
- **Runtime Error** (Ungu / _Purple_): Terjadi _crash_ atau _exception_ saat eksekusi berjalan (misal: _divide by zero_, _index out of bounds_).

### E. Sistem Dark Mode Universal (System-Wide Dark Mode)

Sistem EvalCode dilengkapi dengan fitur tema Terang/Gelap universal yang diatur melalui atribut `[data-bs-theme="dark"]` pada elemen `<html>`.

1. **Penyimpanan Sesi**: Preferensi tema disimpan di dalam `localStorage` (`global_theme`), sehingga tetap aktif antar halaman dan sesi pengguna.
2. **Pencegahan FOUC**: Skrip inisialisasi tema diletakkan pada bagian `<head>` di layout utama (`app.blade.php` dan `sidebar.blade.php`) agar halaman langsung beradaptasi sebelum DOM selesai dirender.
3. **Komponen UI & Monaco Editor**:
   - **Hierarki Visual & Elevasi**: Untuk menghindari tampilan datar dan menyatu dengan latar belakang, `body` menggunakan abu-abu sangat gelap/hitam (`#101010`), sedangkan elemen terangkat seperti `.card`, `.modal-content`, `.dropdown-menu`, dan kontainer tab menggunakan `#1a1a1a` dengan bayangan lembut.
   - **Header Tabel**: Bagian `thead` dan `th` menggunakan latar belakang `#2a2a2a` dengan garis bawah `#444444` agar terpisah secara jelas dari baris data (`#1a1a1a`).
   - **Navigasi Tab/Pills**: Teks tab yang tidak aktif diatur secara eksplisit menggunakan warna `#a0a0a0` agar tetap kontras dan mudah dibaca di atas latar belakang gelap.
   - **Tombol Logout Sidebar**: Diubah secara khusus dari merah terang (`#dc3545`) menjadi merah marun pekat (`#580000`) saat Dark Mode aktif untuk memberikan nuansa gelap yang konsisten dan elegan.
   - **Daftar Soal Pengawas**: Menghapus pembedaan gaya genap/ganjil pada kartu soal di halaman detail ujian pengawas agar semua kartu seragam, memiliki efek *hover* yang responsif, dan konsisten di mode terang maupun gelap.
   - **Badge Peringatan (Kuning)**: Menambahkan aturan khusus pada `app.scss` yang menargetkan `.bg-warning.text-dark` dan `.badge.bg-warning.text-dark` agar teks di dalamnya dipaksa menjadi hitam pekat (`#000000`) dan tebal (seperti pada *badge Similarity*), namun tetap mempertahankan warna teks kuning asli (`text-warning`) pada *badge* transparan seperti status "Belum Dimulai".
   - **Kartu Ringkasan (Kuning)**: Menambahkan aturan khusus untuk `.card.text-dark` agar kartu berlatar belakang kuning terang (seperti "Total Pengawas" di Dashboard Admin) tetap menggunakan teks hitam pekat (`#000000`), mengatasi masalah teks abu-abu yang sulit dibaca di mode gelap.
   - **Teks Identitas Maroon (`.text-unsulbar`)**: Menambahkan *override* warna pada mode gelap menjadi merah koral cerah (`#ff6b6b`). Hal ini mengatasi masalah sulitnya membaca teks maroon gelap pada latar belakang hitam di judul ujian (Workspace Mahasiswa), judul soal (Detail Soal Pengawas), serta ikon daftar ujian.
   - Monaco Editor di halaman Workspace terintegrasi secara dua arah. Perubahan tema global otomatis memicu `window.monaco.editor.setTheme()` untuk transisi instan tanpa _re-render_.
4. **Panduan Pengembangan UI Baru**:
   - Jika menambahkan elemen atau kartu baru, pastikan tidak menggunakan warna latar belakang _hardcoded_ seperti `#ffffff` di CSS inline.
   - Manfaatkan kelas bawaan Bootstrap (seperti `.bg-white`, `.card`, `.table`) yang otomatis dikonversi oleh aturan `[data-bs-theme="dark"]` di `app.scss`.
   - Untuk elemen kustom, tambahkan pemilih spesifik di bawah blok `[data-bs-theme="dark"]` pada `app.scss`.

## 5. Keamanan & Konfigurasi API (Judge0 Autograder)

Untuk mencegah penyalahgunaan kuota API dan menjaga keamanan kredensial, kunci API Judge0 tidak lagi di-*hardcode* di dalam *source code*.
- **Wajib Konfigurasi `.env`**: Setiap *developer* atau instansi yang melakukan *clone* repositori wajib mendaftar di [RapidAPI Judge0 CE](https://rapidapi.com/judge0-official/api/judge0-ce) dan memasukkan kunci API mereka ke variabel `RAPIDAPI_JUDGE0_KEY` di dalam file `.env`.
- **Penanganan Error**: Jika variabel `RAPIDAPI_JUDGE0_KEY` kosong saat mahasiswa melakukan *submit* kode, sistem secara otomatis membatalkan eksekusi dan mengembalikan pesan error JSON yang informatif agar mahasiswa/pengawas segera melengkapi konfigurasi di `.env`.

---

_Dokumen ini diperbarui secara otomatis pada: 2026-05-18. Harap perbarui dokumen ini apabila ada perubahan arsitektur mayor pada masa mendatang._
