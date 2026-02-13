<?php

namespace App\Exports;

use App\Models\LamtimTagihan;
use App\Models\LamtimSiswa;
use App\Models\LamtimRombel;
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

class SiswaReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithCustomStartCell, ShouldAutoSize, WithDrawings
{
    protected string $idSiswa;
    protected string $sekolahNama;
    protected $siswa;
    protected $rombel;
    protected int $rowNumber = 0;
    protected ?string $logo;

    public function __construct(string $idSiswa, string $sekolahNama = '', ?string $logo = null)
    {
        $this->idSiswa = $idSiswa;
        $this->sekolahNama = $sekolahNama ?: 'Sekolah';
        $this->logo = $logo;
        
        $this->siswa = LamtimSiswa::with(['currentRombel.rombel.kelas', 'currentRombel.rombel.jurusan'])->find($idSiswa);
        $this->rombel = $this->siswa->currentRombel->rombel ?? null;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection()
    {
        return LamtimTagihan::where('idSiswa', $this->idSiswa)
            ->where('isActive', 1)
            ->with(['masterPembayaran'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Jenis Pembayaran',
            'Biaya',
            'Terbayar',
            'Tunggakan',
            'Status',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        
        $status = 'Belum Bayar';
        if ($row->status == 1) $status = 'Lunas';
        elseif ($row->status == 2) $status = 'Batal';
        elseif ($row->status == 3) $status = 'Sebagian';

        return [
            $this->rowNumber,
            $row->masterPembayaran->nama ?? '-',
            (float) $row->nominalTagihan,
            (float) ($row->totalSudahBayar ?? 0),
            (float) ($row->nominalTagihan - ($row->totalSudahBayar ?? 0)),
            $status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            8 => [
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
        $siswa = $this->siswa;
        $rombel = $this->rombel;
        
        return [
            AfterSheet::class => function (AfterSheet $event) use ($sekolahNama, $siswa, $rombel) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'F';

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
                $sheet->setCellValue('A2', 'LAPORAN BIAYA PENDIDIKAN SISWA');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '059669']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Row 4-6: Siswa Info
                $sheet->setCellValue('A4', 'Nama Siswa');
                $sheet->setCellValue('B4', ': ' . ($siswa->nama ?? '-'));
                
                $sheet->setCellValue('D4', 'NIS / NISN');
                $sheet->setCellValue('E4', ': ' . ($siswa->nis ?? '-') . ' / ' . ($siswa->nisn ?? '-'));

                $sheet->setCellValue('A5', 'Rombel');
                $classCode = $rombel->kelas->kode ?? '';
                $rombelName = $rombel->nama ?? '-';
                $fullName = $classCode ? "{$classCode} - {$rombelName}" : $rombelName;
                $sheet->setCellValue('B5', ': ' . $fullName);

                $sheet->setCellValue('D5', 'Jurusan');
                $sheet->setCellValue('E5', ': ' . ($rombel->jurusan->nama ?? '-'));

                $sheet->setCellValue('A6', 'Tahun Ajaran');
                
                // Get Tahun Ajaran from Rombel or fallback to Tagihan
                $tahunAjaran = '-';
                if ($rombel && $rombel->tahunAjaran) {
                     $tahunAjaran = $rombel->tahunAjaran->tahun ?? $rombel->tahunAjaran->nama ?? '-';
                } else {
                    // Try to get from tagihans
                    $firstTagihan = LamtimTagihan::where('idSiswa', $siswa->id)->with('tahunAjaran')->first();
                    if ($firstTagihan && $firstTagihan->tahunAjaran) {
                        $tahunAjaran = $firstTagihan->tahunAjaran->tahun ?? $firstTagihan->tahunAjaran->nama ?? '-';
                    }
                }
                
                $sheet->setCellValue('B6', ': ' . $tahunAjaran);

                // Style Info Section
                $sheet->getStyle("A4:F6")->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A4:A6")->getFont()->setBold(true);
                $sheet->getStyle("D4:D6")->getFont()->setBold(true);

                // Row 7: Empty spacer
                $sheet->getRowDimension(7)->setRowHeight(10);

                // Style data rows
                $lastRow = $sheet->getHighestRow();
                $footerRow = 8;

                if ($lastRow > 8) {
                    $sheet->getStyle("A9:F{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'font' => ['size' => 9],
                    ]);

                    for ($row = 9; $row <= $lastRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'ECFDF5'],
                                ],
                            ]);
                        }
                    }

                    // Numeric formatting
                    $sheet->getStyle("C9:E{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'numberFormat' => ['formatCode' => '#,##0'],
                    ]);
                    
                    $sheet->getStyle("A9:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F9:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Add Total Footer
                    $footerRow = $lastRow + 1;
                    $sheet->setCellValue("A{$footerRow}", 'TOTAL');
                    $sheet->mergeCells("A{$footerRow}:B{$footerRow}");
                    
                    // Style Total Label
                    $sheet->getStyle("A{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    // Calculate Totals Manually to ensure value is present
                    $totalBiaya = 0;
                    $totalTerbayar = 0;
                    $totalTunggakan = 0;
                    
                    for ($r = 9; $r <= $lastRow; $r++) {
                        $totalBiaya += $sheet->getCell("C{$r}")->getValue();
                        $totalTerbayar += $sheet->getCell("D{$r}")->getValue();
                        $totalTunggakan += $sheet->getCell("E{$r}")->getValue();
                    }

                    // Set Values
                    $sheet->setCellValue("C{$footerRow}", $totalBiaya);
                    $sheet->setCellValue("D{$footerRow}", $totalTerbayar);
                    $sheet->setCellValue("E{$footerRow}", $totalTunggakan);

                    // Style Total Row
                    $sheet->getStyle("A{$footerRow}:F{$footerRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
                            'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                         'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                    ]);
                    
                    $sheet->getStyle("C{$footerRow}:E{$footerRow}")->getNumberFormat()->setFormatCode('#,##0');
                } else {
                    $footerRow = 8; // If no data, start footer below header
                }

                // --- Payment History Section ---
                $paymentStartRow = $footerRow + 3; // Space after totals
                
                $sheet->setCellValue("A{$paymentStartRow}", 'RIWAYAT PEMBAYARAN');
                $sheet->mergeCells("A{$paymentStartRow}:G{$paymentStartRow}");
                $sheet->getStyle("A{$paymentStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Payment Headers
                $paymentHeaderRow = $paymentStartRow + 1;
                $paymentHeaders = ['No', 'Kode', 'Tanggal', 'Jenis Pembayaran', 'Nominal', 'Metode', 'Status'];
                $colIndex = 'A';
                foreach ($paymentHeaders as $header) {
                    $sheet->setCellValue("{$colIndex}{$paymentHeaderRow}", $header);
                    $colIndex++;
                }

                // Style Payment Headers
                $sheet->getStyle("A{$paymentHeaderRow}:G{$paymentHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '059669'], // emerald-600
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '065F46']],
                    ],
                ]);

                // Retrieve Payments
                $pembayarans = \App\Models\LamtimPembayaran::where('idSiswa', $siswa->id)
                    ->with(['tagihan.masterPembayaran', 'masterPembayaran']) // Load relations for payment type name
                    ->orderBy('tanggalBayar', 'desc')
                    ->get();

                $currentPaymentRow = $paymentHeaderRow + 1;
                if ($pembayarans->count() > 0) {
                    $no = 1;
                    foreach ($pembayarans as $payment) {
                        $sheet->setCellValue("A{$currentPaymentRow}", $no++);
                        $sheet->setCellValue("B{$currentPaymentRow}", $payment->kodePembayaran ?? $payment->kodeBayar ?? '-');
                        $sheet->setCellValue("C{$currentPaymentRow}", $payment->tanggalBayar ? $payment->tanggalBayar->format('d/m/Y') : '-');
                        
                        // Determine payment type name
                        $typeName = $payment->masterPembayaran->nama 
                            ?? $payment->tagihan->masterPembayaran->nama 
                            ?? '-';
                        $sheet->setCellValue("D{$currentPaymentRow}", $typeName);
                        
                        $sheet->setCellValueExplicit(
                            "E{$currentPaymentRow}", 
                            $payment->nominalBayar, 
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                        );
                        
                        $sheet->setCellValue("F{$currentPaymentRow}", $payment->metodeBayar ?? '-');
                        
                        $statusText = match($payment->status) {
                            1 => 'Berhasil',
                            2 => 'Batal',
                            default => 'Pending'
                        };
                        $sheet->setCellValue("G{$currentPaymentRow}", $statusText);

                        $currentPaymentRow++;
                    }

                    // Style Payment Rows
                    $lastPaymentRow = $currentPaymentRow - 1;
                    $sheet->getStyle("A{$paymentHeaderRow}:G{$lastPaymentRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'font' => ['size' => 9],
                    ]);
                    
                    // Numeric Format for Payment Nominal
                    $sheet->getStyle("E{$paymentHeaderRow}:E{$lastPaymentRow}")->getNumberFormat()->setFormatCode('#,##0');
                    
                    // Center Align Specific Columns
                    $sheet->getStyle("A{$paymentHeaderRow}:C{$lastPaymentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$paymentHeaderRow}:G{$lastPaymentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                } else {
                    $sheet->setCellValue("A{$currentPaymentRow}", "Belum ada riwayat pembayaran");
                    $sheet->mergeCells("A{$currentPaymentRow}:G{$currentPaymentRow}");
                    $sheet->getStyle("A{$currentPaymentRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                    ]);
                }
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
