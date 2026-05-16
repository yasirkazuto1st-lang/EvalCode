<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UjianExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $data;
    protected $examInfo;

    public function __construct(array $data, array $examInfo)
    {
        $this->data = $data;
        $this->examInfo = $examInfo;
    }

    public function array(): array
    {
        $header = [
            ['LAPORAN HASIL UJIAN'],
            [''],
            ['Nama Ujian', ': ' . $this->examInfo['judul']],
            ['Durasi', ': ' . $this->examInfo['durasi']],
            ['Passing Grade', ': ' . $this->examInfo['passing_grade']],
            ['Waktu Export', ': ' . $this->examInfo['tanggal']],
            [''],
            ['NIM', 'Nama Mahasiswa', 'Status Kelulusan', 'Skor Akhir'],
        ];

        return array_merge($header, $this->data);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // NIM
            'B' => 35, // Nama Mahasiswa (dikurangi lebarnya agar pas saat diprint)
            'C' => 22, // Status Kelulusan
            'D' => 15, // Skor Akhir
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Mengatur agar saat di-print otomatis muat dalam 1 halaman lebar (Fit to 1 page wide)
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setFitToPage(true);

        // Title Styling
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info Section Styling
        $sheet->getStyle('A3:A6')->getFont()->setBold(true);

        // Table Header Styling (Now at Row 8)
        $sheet->getStyle('A8:D8')->applyFromArray([
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
        ]);

        // Add borders to the data table
        $rowCount = count($this->data) + 8;
        $sheet->getStyle('A8:D' . $rowCount)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Alignment for data columns
        $sheet->getStyle('A9:A' . $rowCount)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C9:D' . $rowCount)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row height
        $sheet->getRowDimension(8)->setRowHeight(25);

        return [];
    }
}
