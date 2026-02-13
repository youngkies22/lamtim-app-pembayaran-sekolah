<?php

namespace App\Exports;

use App\Models\LamtimTagihan;
use App\Helpers\FormatHelper;
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

class RombelReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithCustomStartCell, ShouldAutoSize, WithDrawings
{
    protected array $filters;
    protected int $rowNumber = 0;
    protected string $sekolahNama;
    protected string $rombelNama;
    protected $masters;
    protected ?string $logo;

    public function __construct(array $filters = [], string $sekolahNama = '', string $rombelNama = '', ?string $logo = null)
    {
        $this->filters = $filters;
        $this->sekolahNama = $sekolahNama ?: 'Sekolah';
        $this->rombelNama = $rombelNama;
        $this->logo = $logo;
        
        // Group by slug (Requirement 4 & 9)
        $this->masters = \App\Models\LamtimMasterPembayaran::where('isActive', 1)
            ->whereNotNull('slug')
            ->get()
            ->groupBy('slug')
            ->map(fn($group) => [
                'slug' => $group->first()->slug,
                'ids' => $group->pluck('id')->toArray()
            ])->values();
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function collection()
    {
        $idRombel = $this->filters['idRombel'] ?? null;
        
        // Requirement 11/14: Enforce Rombel in Export
        if (empty($idRombel)) {
            return collect([]);
        }

        // Sync query with ReportController (LamtimSiswaRombel as base)
        $query = \App\Models\LamtimSiswa::query()
            ->join('lamtim_siswa_rombels', 'lamtim_siswas.id', '=', 'lamtim_siswa_rombels.idSiswa')
            ->leftJoin('lamtim_rombels', 'lamtim_siswa_rombels.idRombel', '=', 'lamtim_rombels.id')
            ->leftJoin('lamtim_tagihans', function($join) use ($idRombel) {
                $join->on('lamtim_siswas.id', '=', 'lamtim_tagihans.idSiswa')
                     ->where('lamtim_tagihans.idRombel', '=', $idRombel)
                     ->where('lamtim_tagihans.isActive', '=', 1);
            })
            ->select(
                'lamtim_siswas.id', 
                'lamtim_siswas.nama as siswa_nama', 
                'lamtim_siswas.nis as siswa_nis', 
                'lamtim_rombels.nama as rombel_nama'
            )
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."nominalTagihan"), 0) as total_nominal')
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."totalSudahBayar"), 0) as total_terbayar')
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."totalSisa"), 0) as total_sisa')
            ->where('lamtim_siswa_rombels.idRombel', $idRombel);

        foreach ($this->masters as $master) {
            $slug = $master['slug'];
            $ids = "'" . implode("','", $master['ids']) . "'";
            $query->selectRaw("COALESCE(SUM(CASE WHEN \"lamtim_tagihans\".\"idMasterPembayaran\" IN ($ids) THEN \"lamtim_tagihans\".\"nominalTagihan\" ELSE 0 END), 0) as \"{$slug}\"");
        }

        $query->groupBy('lamtim_siswas.id', 'lamtim_siswas.nama', 'lamtim_siswas.nis', 'lamtim_rombels.nama')
              ->orderBy('siswa_nama');

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Nama Lengkap',
            'Username (NIS)',
            'Rombel',
        ];

        foreach ($this->masters as $master) {
            $headings[] = strtoupper($master['slug']); // Use slug as heading (Requirement 9)
        }

        $headings[] = 'Total Tagihan';
        $headings[] = 'Terbayar';
        $headings[] = 'Sisa';
        $headings[] = 'Status';

        return $headings;
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $rowData = [
            $this->rowNumber,
            $row->siswa_nama,
            $row->siswa_nis,
            $row->rombel_nama,
        ];

        foreach ($this->masters as $master) {
            $slug = $master['slug'];
            $rowData[] = (float) ($row->$slug ?? 0);
        }

        $rowData[] = (float) $row->total_nominal;
        $rowData[] = (float) $row->total_terbayar;
        $rowData[] = (float) $row->total_sisa;
        
        $status = 'Belum Bayar';
        if ($row->total_sisa <= 0) $status = 'Lunas';
        elseif ($row->total_terbayar > 0) $status = 'Sebagian';
        
        $rowData[] = $status;

        return $rowData;
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
        $rombelNama = $this->rombelNama; // Use Rombel Name
        $filters = $this->filters;
        $mastersCount = count($this->masters);
        
        // Calculate last column: 4 (No,Nama,NIS,Rombel) + count(masters) + 4 (Total,Paid,Sisa,Status) = 8 + count
        $lastColIndex = 8 + $mastersCount;
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        return [
            AfterSheet::class => function (AfterSheet $event) use ($sekolahNama, $rombelNama, $filters, $lastCol, $mastersCount) {
                $sheet = $event->sheet->getDelegate();

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
                $sheet->setCellValue('A2', 'LAPORAN TAGIHAN PER ROMBEL (SUMMARY)');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Row 3: Rombel Name (Replaces Tahun Ajaran)
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', strtoupper($rombelNama));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 11, 'bold' => true, 'color' => ['rgb' => '4B5563']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: Empty (Removed Info)
                // $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '9CA3AF']],
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

                // Row 6: Empty spacer
                $sheet->getRowDimension(6)->setRowHeight(6);

                // Style data rows
                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'font' => ['size' => 9],
                    ]);

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

                    // Numeric formatting starting from Column E (index 5) up to status column
                    $firstNumericCol = 'E';
                    $lastNumericColIndex = 4 + $mastersCount + 3; // 4 (base) + count (masters) + 3 (summary: total, paid, sisa)
                    $lastNumericCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastNumericColIndex);
                    
                    // Calculate Totals in PHP to ensure accuracy
                    $columnTotals = [];

                    // Force numeric types for all data cells in numeric columns and accumulate totals
                    for ($row = 8; $row <= $lastRow; $row++) {
                        for ($col = 5; $col <= $lastNumericColIndex; $col++) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                            $cellValue = $sheet->getCell("{$colLetter}{$row}")->getValue();
                            
                            $val = (float) $cellValue;
                            
                            // Accumulate total
                            if (!isset($columnTotals[$col])) {
                                $columnTotals[$col] = 0;
                            }
                            $columnTotals[$col] += $val;

                            // Remove any non-numeric chars if present, though query returns numbers
                            $sheet->setCellValueExplicit(
                                "{$colLetter}{$row}", 
                                $val, 
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                            );
                        }
                    }

                    $sheet->getStyle("{$firstNumericCol}8:{$lastNumericCol}{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'numberFormat' => ['formatCode' => '#,##0'],
                    ]);
                    
                    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C8:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D8:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $statusCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastNumericColIndex + 1);
                    $sheet->getStyle("{$statusCol}8:{$statusCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $lastRow = $sheet->getHighestRow();
                    
                    // Add Total Footer
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", 'TOTAL KESELURUHAN');
                    $sheet->mergeCells("A{$footerRow}:D{$footerRow}"); // Merge No, Name, NIS, Rombel columns
                    
                    // Style Total Label
                    $sheet->getStyle("A{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Write Calculated Sums
                    $colIndex = 5; // Column E
                    while ($colIndex <= 4 + $mastersCount + 3) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        
                        $totalValue = $columnTotals[$colIndex] ?? 0;
                        $sheet->setCellValueExplicit(
                            "{$colLetter}{$footerRow}", 
                            $totalValue, 
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                        );
                        
                        $colIndex++;
                    }

                    // Style Total Row
                    $sheet->getStyle("A{$footerRow}:{$lastCol}{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                            'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                         'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'], // Gray-100
                        ],
                    ]);
                    
                    // Apply Number Format to Total Row
                    $sheet->getStyle("E{$footerRow}:{$lastCol}{$footerRow}")->getNumberFormat()->setFormatCode('#,##0');

                    // Adjust print footer position
                    $printFooterRow = $footerRow + 2;
                } else {
                     $printFooterRow = $lastRow + 2;
                }

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
            } elseif (file_exists(public_path($this->logo))) {
                $logoPath = public_path($this->logo);
            }
        }

        if ($logoPath) {
            $drawing = new Drawing();
            $drawing->setName('Logo Sekolah');
            $drawing->setDescription('Logo');
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
