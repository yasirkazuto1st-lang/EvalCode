# EvalCode - Panduan Pengembangan & Dokumentasi Proyek

Dokumen ini berfungsi sebagai panduan referensi sentral untuk memudahkan para *developer*, *maintenance engineer*, atau AI *assistant* di masa depan untuk memahami arsitektur, teknologi, dan alur kerja utama dalam proyek **EvalCode**.

## 1. Project Overview (Gambaran Umum)

**EvalCode** adalah platform ujian *coding* (*competitive programming/assessment*) yang memungkinkan dosen atau panitia untuk menyelenggarakan ujian berbasis pemrograman, dimana mahasiswa dapat mengetikkan kode (*source code*) lalu dievaluasi secara otomatis (melalui sistem *autograder*).

**Target Pengguna**:
1. **Admin**: Mengelola data ujian, soal, test case, serta akun pengguna.
2. **Pengawas**: Memonitor jalannya ujian, mengontrol status ujian (mulai/jeda/selesai), serta meninjau hasil nilai.
3. **Mahasiswa**: Peserta yang masuk ke ujian menggunakan token *real-time* dan mengerjakan soal pemrograman dengan *compiler* di dalam *browser*.

## 2. Tech Stack (Teknologi Utama)

- **Backend Framework**: Laravel 13 (PHP)
- **Frontend**: Blade Templating Engine + Bootstrap 5 (Styling) + React (sedikit implementasi khusus pada bagian Workspace)
- **Database**: MySQL (relasional)
- **Code Editor**: Monaco Editor (untuk *syntax highlighting* di halaman pengerjaan)
- **Autograder API**: Judge0 (berjalan di background untuk mengeksekusi kode berbagai bahasa pemrograman seperti **Python, Java, C++, dan Dart**)
- **Exporting**: Laravel Excel / PhpSpreadsheet (ekspor laporan XLSX).

## 3. Architecture & Core Workflow (Arsitektur & Alur Kerja)

### A. Alur Ujian (Role Mahasiswa)
1. Mahasiswa login dan masuk ke **Dashboard**.
2. Mahasiswa melihat daftar ujian aktif dan mengklik tombol "Masuk Ujian".
3. Mahasiswa harus memasukkan **Token** ujian yang valid (didapatkan dari Pengawas melalui proyektor/layar pengawas).
4. Jika valid, Mahasiswa masuk ke halaman **Detail Ujian**, melihat leaderboard, dan daftar soal.
5. Mahasiswa memilih soal, lalu membuka **Workspace**. Di sini Mahasiswa mengetik kode, melakukan *Run/Compile* (ke Judge0), lalu menekan tombol *Submit*.
6. Submisi akan dievaluasi terhadap seluruh **Test Case** di latar belakang. Skor dihitung otomatis berdasarkan persentase *Test Case* yang benar (*Accepted*).

### B. Alur Monitoring & Kontrol (Role Pengawas)
1. Pengawas masuk ke menu **Monitoring Ujian**.
2. Pengawas memilih ujian tertentu dan dapat mengubah status: **Start** (mulai), **Pause** (jeda), **Finish** (selesai).
3. Ketika status ujian adalah *Active*, sistem akan **menerbitkan Token Ujian (6 karakter acak)** yang diperbarui secara *real-time* (AJAX polling setiap 60 detik).
4. Pengawas dapat melihat nilai (*real-time leaderboard*) dan dapat melakukan **Override Skor** (memberikan nilai manual/kompensasi beserta catatan).

### C. Alur Master Data (Role Admin)
1. Admin mengelola (CRUD) master **Ujian**.
2. Di dalam setiap Ujian, Admin mengelola (CRUD) **Soal** (berupa lampiran file PDF dan *bobot nilai*).
3. Di dalam setiap Soal, Admin mengelola (CRUD) **Test Case** (berupa *Input* dan *Expected Output*).
4. Admin dapat mengunduh **Laporan Excel** (*stacked layout*: Tabel Peserta + Tabel Statistik Soal).
5. Admin dapat mengelola akun **User**.

## 4. Database Schema Overview (Skema Database)

Sistem menggunakan tipe penilaian berbasis **integer murni**.
- `users`: Tabel akun (`user_id`, `name`, `nim_nip`, `role`, `password`, `last_session_id`).
- `ujians`: Tabel ujian (`ujian_id`, `judul`, `durasi`, `status`, `passing_grade` (integer)).
- `soals`: Tabel soal (`soal_id`, `ujian_id`, `nama_soal`, `soal_pdf`, `bobot_nilai` (integer)).
- `test_cases`: Tabel skenario tes (`test_case_id`, `soal_id`, `input`, `expected_output`).
- `tokens`: Tabel token *real-time* (`token_id`, `ujian_id`, `kode_token`, `status_aktif`).
- `submissions`: Tabel rekam jejak jawaban Mahasiswa (`submission_id`, `user_id`, `soal_id`, `source_code`, `language_id`, `status` (String seperti "Accepted", "Wrong Answer"), `skor` (integer), `execution_time`, `memory`, `similarity_index` (untuk deteksi plagiarisme)).

## 5. Directory Structure (Struktur Direktori Kunci)

- `app/Http/Controllers/`: 
  - `AdminController.php`
  - `PengawasUjianController.php`
  - `MahasiswaUjianController.php`
  - `AuthController.php`
- `app/Models/`: `Ujian.php`, `Soal.php`, `TestCase.php`, `User.php`, `Token.php`
- `app/Exports/`: `UjianExport.php` (Pengaturan ekspor *layout* dua tabel)
- `routes/web.php`: Manajemen *routing* berdasarkan *middleware* peran (`Admin`, `Pengawas`, `Mahasiswa`).
- `resources/views/`: 
  - `admin/`: View untuk master data.
  - `pengawas/`: View untuk monitoring ujian dan token generator.
  - `mahasiswa/`: View untuk *workspace code editor*.

## 6. Fitur Teknis Lanjutan

- **Integrasi Judge0 API**: File `MahasiswaUjianController.php` (metode `submitCode`) menangani komunikasi sinkronisasi *compile* dan *run* ke server Judge0. Respons dari Judge0 dikonversi (melalui metode `resolveJudge0StatusForEvalcode`) agar ramah pengguna (contoh: memisahkan *Compilation Error* dengan *Runtime Error*).
- **Deteksi Plagiarisme Otomatis**: EvalCode menggunakan algoritma **Jaccard Similarity** pada *source code* setiap submisi (di *MahasiswaUjianController* metode `submitCode`). Kode baru dibandingkan dengan seluruh kode *Accepted* dari *user* lain pada ujian yang sama.
- **Single-Session Enforcement**: Setiap akun hanya boleh login pada satu *device/browser*. Fitur ini diatur pada `AuthController.php` menggunakan pelacakan `$user->last_session_id`.

## 7. Panduan Warna dan Label (UI Indicators)

Sistem menggunakan berbagai warna (*badge*) dan label untuk memudahkan identifikasi status di dalam tabel dan halaman *monitoring*:

### A. Status Ujian
- **Active** (Aktif): Ujian sedang berlangsung. Biasanya diwakili dengan UI *highlight* warna hijau (*Success*).
- **Closed** (Ditutup sementara): Ujian sedang di-pause oleh pengawas. Diwakili dengan warna kuning (*Warning*).
- **Finished** (Selesai): Ujian telah berakhir secara permanen. Diwakili dengan warna biru (*Primary*).

### B. Indeks Similaritas (Plagiarisme)
Indeks kesamaan (*similarity*) dikategorikan dalam 3 rentang warna untuk membantu Pengawas mengidentifikasi kecurangan:
- **>= 70%** (Merah / *Danger*): Indikasi plagiarisme sangat tinggi.
- **40% - 69%** (Kuning / *Warning*): Indikasi plagiarisme sedang (perlu peninjauan manual).
- **< 40%** (Hijau / *Success*): Kode dianggap aman atau kemiripan wajar.

### C. Tingkat Kesulitan Soal (Berdasarkan Kelulusan)
Diukur berdasarkan persentase *user* yang berhasil menyelesaikan soal tersebut (*Accepted*) berbanding dengan total peserta ujian.
- **0% (Belum ada)**: Kategori `Belum Dikerjakan` (Abu-abu / *Secondary*).
- **<= 25%**: Kategori `Sangat Sulit` (Merah / *Danger*).
- **26% - 50%**: Kategori `Sulit` (Kuning / *Warning*).
- **51% - 75%**: Kategori `Normal` (Biru / *Primary*).
- **> 75%**: Kategori `Gampang` (Hijau / *Success*).

### D. Status Submisi Kode (Evaluasi Judge0)
- **Accepted** (Hijau / *Success*): Kode berjalan sempurna dan melewati semua *Test Case*.
- **Wrong Answer** (Merah / *Danger*): Kode berjalan, tetapi *output* tidak sesuai dengan *Expected Output*.
- **Time Limit Exceeded** (Kuning / *Warning*): Kode berjalan terlalu lama melebihi batas waktu maksimal (*timeout*).
- **Compilation Error** (Abu-abu / *Secondary*): Kode gagal di-compile (terjadi *syntax error* pada C++/Java, dll).
- **Runtime Error** (Ungu / *Purple*): Terjadi *crash* atau *exception* saat eksekusi berjalan (misal: *divide by zero*, *index out of bounds*).

---
*Dokumen ini diperbarui secara otomatis pada: 2026-05-17. Harap perbarui dokumen ini apabila ada perubahan arsitektur mayor pada masa mendatang.*
