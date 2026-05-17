<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Class UjianExport
 * Menangani pembuatan format laporan Excel (XLSX) yang berisi daftar rekapitulasi peserta
 * ujian beserta statistik kelulusan soal secara bersusun (stacked).
 */
class UjianExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $data;
    protected $examInfo;
    protected $soalData;

    public function __construct(array $data, array $examInfo, array $soalData = [])
    {
        $this->data = $data;
        $this->examInfo = $examInfo;
        $this->soalData = $soalData;
    }

    /**
     * Membangun struktur baris Excel secara berurutan.
     * Mulai dari Info Ujian, Header Peserta, Data Peserta, Spacer Baris Kosong,
     * Header Statistik Soal, hingga Data Statistik Soal.
     * 
     * @return array
     */
    public function array(): array
    {
        // 1. Header & Info Section
        $header = [
            ['LAPORAN HASIL UJIAN'],
            [''],
            ['Nama Ujian', ': ' . $this->examInfo['judul']],
            ['Durasi', ': ' . $this->examInfo['durasi']],
            ['Passing Grade', ': ' . $this->examInfo['passing_grade']],
            ['Waktu Export', ': ' . $this->examInfo['tanggal']],
            [''],
            ['NIM', 'Nama Mahasiswa', 'Status Kelulusan', 'Skor Akhir'], // Row 8
        ];

        // 2. Main Participants Data
        $rows = array_merge($header, $this->data);

        // 3. Spacer Rows before Table 2
        $rows[] = ['', '', '', '']; // Blank row
        $rows[] = ['', '', '', '']; // Blank row

        // 4. Table 2 Header
        $rows[] = ['No. Soal', 'Nama Soal', 'Menyelesaikan', 'Kategori'];

        // 5. Table 2 Data
        foreach ($this->soalData as $soal) {
            $rows[] = [
                $soal['no_soal'],
                $soal['nama_soal'],
                $soal['selesai'],
                $soal['kategori'],
            ];
        }

        return $rows;
    }

    /**
     * Mengatur lebar kolom agar rapi saat dilihat maupun di-print.
     * 
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // NIM / No. Soal
            'B' => 35, // Nama Mahasiswa / Nama Soal
            'C' => 22, // Status Kelulusan / Menyelesaikan
            'D' => 15, // Skor Akhir / Kategori
        ];
    }

    /**
     * Menerapkan gaya visual, border, warna header, dan pengaturan print area pada sheet Excel.
     * Menghitung posisi baris secara dinamis bergantung pada jumlah peserta untuk melukis 
     * tabel statistik soal di bawahnya.
     * 
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Mengatur agar saat di-print otomatis muat dalam 1 halaman lebar (Fit to 1 page wide)
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setFitToPage(true);

        // Title Styling (Merge across Columns A to D)
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info Section Styling
        $sheet->getStyle('A3:A6')->getFont()->setBold(true);

        // Table Header Styling Definition
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '800000'], // Maroon
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        // ==========================================
        // STYLING TABLE 1 (Peserta)
        // ==========================================
        $sheet->getStyle('A8:D8')->applyFromArray($headerStyle);
        $sheet->getRowDimension(8)->setRowHeight(25);

        $pCount = count($this->data);
        $endTable1 = 8 + $pCount;

        if ($pCount > 0) {
            $sheet->getStyle('A8:D' . $endTable1)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $sheet->getStyle('A9:A' . $endTable1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C9:D' . $endTable1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->getStyle('A8:D8')->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // ==========================================
        // STYLING TABLE 2 (Statistik Soal)
        // ==========================================
        $headerTable2 = $endTable1 + 3; // 2 blank rows spacer
        $sheet->getStyle('A' . $headerTable2 . ':D' . $headerTable2)->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerTable2)->setRowHeight(25);

        $sCount = count($this->soalData);
        $endTable2 = $headerTable2 + $sCount;

        if ($sCount > 0) {
            $sheet->getStyle('A' . $headerTable2 . ':D' . $endTable2)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $sheet->getStyle('A' . ($headerTable2 + 1) . ':A' . $endTable2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . ($headerTable2 + 1) . ':D' . $endTable2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->getStyle('A' . $headerTable2 . ':D' . $headerTable2)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        return [];
    }
}
