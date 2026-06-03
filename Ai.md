# EvalCode - Panduan Pengembangan & Dokumentasi Proyek

Dokumen ini berfungsi sebagai panduan referensi sentral untuk memudahkan para _developer_, _maintenance engineer_, atau AI _assistant_ di masa depan untuk memahami arsitektur, teknologi, dan alur kerja utama dalam proyek **EvalCode**.

## 1. Project Overview (Gambaran Umum)

**EvalCode** adalah platform ujian _coding_ (_competitive programming/assessment_) yang memungkinkan dosen atau panitia untuk menyelenggarakan ujian berbasis pemrograman, dimana mahasiswa dapat mengetikkan kode (_source code_) lalu dievaluasi secara otomatis (melalui sistem _autograder_).

**Target Pengguna**:

1. **Admin**: Mengelola data ujian, soal, test case, serta akun pengguna.
2. **Pengawas**: Memonitor jalannya ujian, mengontrol status ujian (mulai/jeda/selesai), mereset kesempatan submit mahasiswa, serta meninjau hasil nilai.
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
4. Jika valid, Mahasiswa masuk ke halaman **Detail Ujian**, melihat leaderboard, sisa waktu hitung mundur, dan daftar soal dengan status kesempatan submit (maksimal 3 kali per soal).
5. Mahasiswa memilih soal, lalu membuka **Workspace**. Di sini Mahasiswa mengetik kode, melihat sisa waktu, melakukan _Run/Compile_ (ke Judge0), lalu menekan tombol _Submit_ (maksimal 3 kali submit per soal).
6. Submisi akan dievaluasi terhadap seluruh **Test Case** di latar belakang. Skor dihitung otomatis berdasarkan persentase _Test Case_ yang benar (_Accepted_).
7. Jika waktu habis, mahasiswa otomatis dikeluarkan dari workspace ke dashboard dan status pengerjaan soal di-lock.

### B. Alur Monitoring & Kontrol (Role Pengawas)

1. Pengawas masuk ke menu **Monitoring Ujian**.
2. Pengawas memilih ujian tertentu dan dapat mengubah status: **Start** (mulai), **Pause** (jeda), **Finish** (selesai).
3. Ketika status ujian adalah _Active_, sistem akan **menerbitkan Token Ujian (6 karakter acak)** yang diperbarui secara _real-time_ (AJAX polling setiap 60 detik) dan menampilkan hitung mundur waktu ujian di samping kiri token secara horizontal.
4. Pengawas dapat melihat nilai peserta secara real-time.
5. Pengawas dapat memantau **Status Kesempatan per Soal** (menampilkan badge jumlah percobaan `X / 3` per soal untuk tiap mahasiswa) dan melakukan **Reset Kesempatan** jika diperlukan untuk memberikan kesempatan mencoba lagi.
6. Pengawas dapat melakukan **Override Skor** (memberikan nilai manual/kompensasi beserta catatan).
7. Pengawas dapat melakukan **Penghapusan Submisi** tertentu secara permanen dari database. Tindakan ini akan secara otomatis memperbarui nilai total peserta, statistik soal, dan leaderboard ujian secara real-time.

### C. Alur Master Data (Role Admin)

1. Admin mengelola (CRUD) master **Ujian**. Jika status ujian diubah menjadi 'active' (berjalan) melalui panel edit Admin, sistem secara otomatis akan menginisialisasi parameter hitung mundur (countdown) dari durasi penuh dan menerbitkan token aktif baru.
2. Di dalam setiap Ujian, Admin mengelola (CRUD) **Soal** (berupa lampiran file PDF dan _bobot nilai_).
3. Di dalam setiap Soal, Admin mengelola (CRUD) **Test Case** (berupa _Input_ dan _Expected Output_).
4. Admin dapat mengunduh **Laporan Excel** (_stacked layout_: Tabel Peserta + Tabel Statistik Soal). Submisi yang di-reset oleh pengawas tetap dihitung dalam leaderboard, statistik, dan ekspor excel (fitur reset hanya mengembalikan kuota percobaan agar mahasiswa mendapat kesempatan submit baru).
5. Admin dapat mengelola akun **User**.

## 4. Database Schema Overview (Skema Database)

Sistem menggunakan tipe penilaian berbasis **integer murni**.

- `users`: Tabel akun (`user_id`, `name`, `nim_username`, `role`, `password`, `last_session_id`).
- `ujians`: Tabel ujian (`ujian_id`, `judul`, `durasi`, `status`, `passing_grade` (integer), `max_attempt` (integer, default 3, batas maksimal submit per soal), `started_at` (timestamp, waktu mulai ujian aktif), `sisa_waktu` (integer, sisa waktu dalam detik)).
- `soals`: Tabel soal (`soal_id`, `ujian_id`, `nama_soal`, `soal_pdf`, `bobot_nilai` (integer)).
- `test_cases`: Tabel skenario tes (`test_case_id`, `soal_id`, `input`, `expected_output`).
- `tokens`: Tabel token _real-time_ (`token_id`, `ujian_id`, `kode_token`, `status_aktif`).
- `submissions`: Tabel rekam jejak jawaban Mahasiswa (`submission_id`, `user_id`, `soal_id`, `source_code`, `language_id`, `status` (String seperti "Accepted", "Wrong Answer"), `skor` (integer), `execution_time`, `memory`, `is_reset` (boolean, penanda jika kesempatan telah di-reset oleh pengawas), `similarity_index` (untuk deteksi plagiarisme)).

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
    - `pengawas/`: View untuk monitoring ujian, reset status percobaan, dan token generator.
    - `mahasiswa/`: View untuk _detail ujian_ dan _workspace code editor_.

## 6. Fitur Teknis Lanjutan

- **Integrasi Judge0 API**: File `MahasiswaUjianController.php` (metode `submitCode`) menangani komunikasi sinkronisasi _compile_ dan _run_ ke server Judge0. Respons dari Judge0 dikonversi (melalui metode `resolveJudge0StatusForEvalcode`) agar ramah pengguna (contoh: memisahkan _Compilation Error_ dengan _Runtime Error_).
- **Deteksi Plagiarisme Otomatis**: EvalCode menggunakan algoritma **Jaccard Similarity** pada _source code_ setiap submisi (di _MahasiswaUjianController_ metode `submitCode`). Kode baru dibandingkan dengan seluruh kode _Accepted_ dari _user_ lain pada ujian yang sama (termasuk submisi yang telah di-reset kesempatannya).
- **Single-Session Enforcement**: Setiap akun hanya boleh login pada satu _device/browser_. Fitur ini diatur pada `AuthController.php` menggunakan pelacakan `$user->last_session_id`.
- **Sistem Countdown Ujian & Auto-Pause**:
    - Menggunakan kombinasi kolom `started_at` dan `durasi` untuk menghitung sisa waktu secara dinamis: `Sisa = (Durasi * 60) - (Waktu Sekarang - started_at)`.
    - Setiap kali ujian dimulai ulang (`active`), hitung mundur dimulai kembali dari durasi penuh ujian, dan jika di-pause manual (`closed`) atau habis, sisa waktu di-reset ke 0.
    - Tampilan countdown di dashboard pengawas diletakkan secara horizontal di samping kiri token ujian.
    - Mahasiswa dan Pengawas melakukan AJAX polling setiap 5 detik ke `/ujian/{id}/status` untuk mensinkronisasi timer klien dengan database guna mencegah _drift_ / efek tab browser tertidur (_sleep_).
    - Ketika status ujian tidak lagi aktif, mahasiswa otomatis dikeluarkan dari workspace kembali ke dashboard dengan pesan alert terintegrasi.
- **Batas Percobaan & Reset Kesempatan**:
    - Mahasiswa dibatasi submit untuk setiap soal sesuai dengan nilai `max_attempt` pada ujian terkait (default: 3, dapat disetel oleh Admin melalui form tambah/edit ujian). Informasi status sisa percobaan (`X / max_attempt`) ditampilkan di daftar soal detail ujian dan di atas panel submit workspace mahasiswa.
    - Jika batas tercapai, tombol submit di-disable dan mahasiswa tidak dapat mengirimkan jawaban lagi.
    - Pengawas memiliki otorisasi untuk melakukan reset kesempatan mahasiswa pada soal tertentu. Tindakan ini menandai seluruh submisi mahasiswa pada soal tersebut dengan status `is_reset = true`.
    - Tampilan tabel reset ("Status Kesempatan per Soal") diposisikan tepat di atas tabel "Riwayat Submission" di menu kolaps baris peserta pada monitoring pengawas.
    - Seluruh perhitungan statistik soal, leaderboard, skor tertinggi, laporan Excel admin, dan perbandingan plagiarisme tetap mencakup submisi yang telah di-reset. Nilai kolom `is_reset` hanya digunakan untuk menyaring dan menghitung sisa kuota percobaan mahasiswa secara independen.
    - Tabel "Riwayat Submission" tetap menampilkan status asli dari Judge0 (seperti `Accepted`, `Wrong Answer`, dll.) untuk seluruh baris data.
    - Di sudut kanan atas tabel "Riwayat Submission" (pada detail monitoring Pengawas and Admin) tersedia dropdown pilihan soal untuk menyaring daftar riwayat secara dinamis dan memperbarui pagination tabel secara otomatis.
- **Aksi Submisi Pengawas & Penghapusan Permanen**:
    - Pada tabel "Riwayat Submission" milik Pengawas, tombol aksi (**Lihat Kode**, **Override Skor**, dan **Hapus Submisi**) ditampilkan sebagai tombol ikon-saja minimalis ($32\times32$ px) dengan tooltip petunjuk guna menjaga kerapihan visual tabel.
    - Fitur **Hapus Submisi** memungkinkan pengawas menghapus rekaman jawaban tertentu secara permanen dari database. Ketika tombol hapus diklik, spinner pemrosesan kecil akan muncul di dalam tombol tanpa teks tambahan untuk mencegah pergeseran tata letak kolom tabel. Setelah terhapus, nilai total mahasiswa dan leaderboard akan disinkronisasikan ulang secara otomatis.

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
    - **Navigasi Sidebar**: Khusus pada mode gelap (_Dark Mode_), bayangan kotak (_box shadow_) pada item navigasi sidebar dinonaktifkan (`box-shadow: none !important`) agar tampilan visual sidebar tampak lebih bersih dan minimalis.
    - **Daftar Soal Pengawas**: Menghapus pembedaan gaya genap/ganjil pada kartu soal di halaman detail ujian pengawas agar semua kartu seragam, memiliki efek _hover_ yang responsif, dan konsisten di mode terang maupun gelap.
    - **Badge Peringatan (Kuning)**: Menambahkan aturan khusus pada `app.scss` yang menargetkan `.bg-warning.text-dark` dan `.badge.bg-warning.text-dark` agar teks di dalamnya dipaksa menjadi hitam pekat (`#000000`) dan tebal (seperti pada _badge Similarity_), namun tetap mempertahankan warna teks kuning asli (`text-warning`) pada _badge_ transparan seperti status "Belum Dimulai".
    - **Kartu Ringkasan (Kuning)**: Menambahkan aturan khusus untuk `.card.text-dark` agar kartu berlatar belakang kuning terang (seperti "Total Pengawas" di Dashboard Admin) tetap menggunakan teks hitam pekat (`#000000`), mengatasi masalah teks abu-abu yang sulit dibaca di mode gelap.
    - **Teks Identitas Maroon (`.text-unsulbar`)**: Menambahkan _override_ warna pada mode gelap menjadi merah koral cerah (`#ff6b6b`). Hal ini mengatasi masalah sulitnya membaca teks maroon gelap pada latar belakang hitam di judul ujian (Workspace Mahasiswa), judul soal (Detail Soal Pengawas), serta ikon daftar ujian.
    - Monaco Editor di halaman Workspace terintegrasi secara dua arah. Perubahan tema global otomatis memicu `window.monaco.editor.setTheme()` untuk transisi instan tanpa _re-render_.
4. **Panduan Pengembangan UI Baru**:
    - Jika menambahkan elemen atau kartu baru, pastikan tidak menggunakan warna latar belakang _hardcoded_ seperti `#ffffff` di CSS inline.
    - Manfaatkan kelas bawaan Bootstrap (seperti `.bg-white`, `.card`, `.table`) yang otomatis dikonversi oleh aturan `[data-bs-theme="dark"]` di `app.scss`.
    - Untuk elemen kustom, tambahkan pemilih spesifik di bawah blok `[data-bs-theme="dark"]` pada `app.scss`.

## 8. Keamanan & Konfigurasi API (Judge0 Autograder)

Untuk mencegah penyalahgunaan kuota API dan menjaga keamanan kredensial, kunci API Judge0 tidak lagi di-_hardcode_ di dalam _source code_.

- **Wajib Konfigurasi `.env`**: Setiap _developer_ atau instansi yang melakukan _clone_ repositori wajib mendaftar di [RapidAPI Judge0 CE](https://rapidapi.com/judge0-official/api/judge0-ce) dan memasukkan kunci API mereka ke variabel `RAPIDAPI_JUDGE0_KEY` di dalam file `.env`.
- **Penanganan Error**: Jika variabel `RAPIDAPI_JUDGE0_KEY` kosong saat mahasiswa melakukan _submit_ kode, sistem secara otomatis membatalkan eksekusi dan mengembalikan pesan error JSON yang informatif agar mahasiswa/pengawas segera melengkapi konfigurasi di `.env`.

---

_Dokumen ini diperbarui secara otomatis pada: 2026-05-22. Harap perbarui dokumen ini apabila ada perubahan arsitektur mayor pada masa mendatang._
