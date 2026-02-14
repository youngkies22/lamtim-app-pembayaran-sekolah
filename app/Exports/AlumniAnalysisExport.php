<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Collection;

class AlumniAnalysisExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithCustomStartCell, ShouldAutoSize, WithDrawings
{
    protected Collection $data;
    protected array $summary;
    protected string $sekolahNama;
    protected ?string $logo;
    protected ?string $tahunAngkatan;
    protected int $rowNumber = 0;

    public function __construct(array $data, string $sekolahNama = 'Sekolah', ?string $logo = null, ?string $tahunAngkatan = null)
    {
        $this->data = collect($data['byYear']);
        $this->summary = $data['summary'];
        $this->sekolahNama = $sekolahNama;
        $this->logo = $logo;
        $this->tahunAngkatan = $tahunAngkatan;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tahun Angkatan',
            'Jumlah Siswa',
            'Total Tagihan',
            'Total Terbayar',
            'Total Tunggakan',
            'Rasio Pelunasan (%)'
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        $totalTagihan = (float) $row['total_tagihan'];
        $totalTerbayar = (float) $row['total_terbayar'];
        $rasio = $totalTagihan > 0 ? round(($totalTerbayar / $totalTagihan) * 100) : 0;

        return [
            $this->rowNumber,
            $row['tahunAngkatan'] ?: 'N/A',
            $row['total_siswa'],
            $totalTagihan,
            $totalTerbayar,
            (float) $row['total_tunggakan'],
            $rasio . '%'
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'], // emerald-600
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '065F46']],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $sekolahNama = $this->sekolahNama;
        $tahunAngkatan = $this->tahunAngkatan;
        $summary = $this->summary;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($sekolahNama, $tahunAngkatan, $summary) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'G';
                $lastRow = $sheet->getHighestRow();

                // Row 1: School Name
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', strtoupper($sekolahNama));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Row 2: Report Title
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI KEUANGAN ALUMNI');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Row 3: Subtitle (Filter)
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', $tahunAngkatan ? "ANGKATAN: {$tahunAngkatan}" : "SELURUH ANGKATAN");
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 11, 'bold' => true, 'color' => ['rgb' => '4B5563']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5: Divider line
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '059669']],
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(8);

                // Styling Data Table
                if ($lastRow >= 8) {
                    $sheet->getStyle("A8:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'font' => ['size' => 10],
                    ]);

                    // Zebra Striping
                    for ($row = 8; $row <= $lastRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'ECFDF5'],
                                ],
                            ]);
                        }
                    }

                    // Numeric Column Formatting
                    $sheet->getStyle("D8:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("D8:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    
                    // Center Alignment for specific columns
                    $sheet->getStyle("A8:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G8:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Add Total Footer
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", 'TOTAL KESELURUHAN');
                    $sheet->mergeCells("A{$footerRow}:B{$footerRow}");
                    
                    $sheet->setCellValue("C{$footerRow}", $summary['totalSiswa']);
                    $sheet->setCellValue("D{$footerRow}", $summary['totalTagihan']);
                    $sheet->setCellValue("E{$footerRow}", $summary['totalTerbayar']);
                    $sheet->setCellValue("F{$footerRow}", $summary['totalTunggakan']);
                    
                    $sheet->getStyle("A{$footerRow}:{$lastCol}{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_DOUBLE],
                            'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);
                    $sheet->getStyle("C{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$footerRow}:F{$footerRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("D{$footerRow}:F{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $printFooterRow = $sheet->getHighestRow() + 2;
                $sheet->mergeCells("A{$printFooterRow}:{$lastCol}{$printFooterRow}");
                $sheet->setCellValue("A{$printFooterRow}", 'Dicetak pada: ' . now()->format('d/m/Y H:i:s'));
                $sheet->getStyle("A{$printFooterRow}")->applyFromArray([
                    'font' => ['size' => 8, 'italic' => true, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
            },
        ];
    }

    public function drawings()
    {
        $drawings = [];
        $logoPath = null;

        if ($this->logo) {
            if (file_exists(storage_path('app/public/' . $this->logo))) {
                $logoPath = storage_path('app/public/' . $this->logo);
            } elseif (file_exists(public_path('storage/' . $this->logo))) {
                $logoPath = public_path('storage/' . $this->logo);
            }
        }

        if ($logoPath) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('B1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawings[] = $drawing;
        }

        return $drawings;
    }
}
