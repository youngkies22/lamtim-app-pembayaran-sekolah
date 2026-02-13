<?php

namespace App\Exports;

use App\Models\LamtimPembayaran;
use App\Helpers\FormatHelper;
use Maatwebsite\Excel\Concerns\FromQuery;
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

class PembayaranExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithCustomStartCell, ShouldAutoSize, WithDrawings
{
    protected array $filters;
    protected int $rowNumber = 0;
    protected string $sekolahNama;
    protected string $tahunAjaran;
    protected ?string $logo;

    public function __construct(array $filters = [], string $sekolahNama = '', string $tahunAjaran = '', ?string $logo = null)
    {
        $this->filters = $filters;
        $this->sekolahNama = $sekolahNama ?: 'Sekolah';
        $this->tahunAjaran = $tahunAjaran;
        $this->logo = $logo;
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function query()
    {
        $query = LamtimPembayaran::query()
            ->with(['siswa', 'masterPembayaran'])
            ->where('isActive', 1)
            ->orderBy('tanggalBayar', 'desc');

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['isVerified']) && $this->filters['isVerified'] !== '') {
            $query->where('isVerified', $this->filters['isVerified']);
        }

        if (!empty($this->filters['startDate'])) {
            $query->whereDate('tanggalBayar', '>=', $this->filters['startDate']);
        }

        if (!empty($this->filters['endDate'])) {
            $query->whereDate('tanggalBayar', '<=', $this->filters['endDate']);
        }

        if (isset($this->filters['jenisPembayaran']) && $this->filters['jenisPembayaran'] !== '') {
            $kode = $this->filters['jenisPembayaran'];
            $query->whereHas('masterPembayaran', function ($q) use ($kode) {
                $q->where('kode', $kode);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Pembayaran',
            'Nama Siswa',
            'NIS',
            'Jenis Pembayaran',
            'Nominal',
            'Metode Bayar',
            'Tanggal',
            'Status',
            'Verifikasi',
            'Keterangan',
        ];
    }

    public function map($pembayaran): array
    {
        $this->rowNumber++;

        $statusLabels = [0 => 'Pending', 1 => 'Berhasil', 2 => 'Dibatalkan'];
        $verifikasiLabels = [0 => 'Belum', 1 => 'Terverifikasi'];

        return [
            $this->rowNumber,
            $pembayaran->kodePembayaran,
            $pembayaran->siswa->nama ?? '-',
            $pembayaran->siswa->nis ?? '-',
            $pembayaran->masterPembayaran->nama ?? '-',
            $pembayaran->nominalBayar,
            $pembayaran->metodeBayar ?? '-',
            FormatHelper::date($pembayaran->tanggalBayar, 'd/m/Y'),
            $statusLabels[$pembayaran->status] ?? '-',
            $verifikasiLabels[$pembayaran->isVerified] ?? '-',
            $pembayaran->keterangan ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Row 7 is the heading row (startCell is A7)
        return [
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1D4ED8']],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        $sekolahNama = $this->sekolahNama;
        $tahunAjaran = $this->tahunAjaran;
        $filters = $this->filters;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($sekolahNama, $tahunAjaran, $filters) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'K'; // 11 columns (A-K)

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
                $sheet->setCellValue('A2', 'LAPORAN PEMBAYARAN');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(22);

                // Row 3: Tahun Ajaran
                $sheet->mergeCells("A3:{$lastCol}3");
                $tahunText = $tahunAjaran ? "Tahun Ajaran {$tahunAjaran}" : '';
                $sheet->setCellValue('A3', $tahunText);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: Period Info
                $sheet->mergeCells("A4:{$lastCol}4");
                $periodeText = '';
                if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
                    $periodeText = "Periode: {$filters['startDate']} s/d {$filters['endDate']}";
                } elseif (!empty($filters['startDate'])) {
                    $periodeText = "Dari: {$filters['startDate']}";
                } elseif (!empty($filters['endDate'])) {
                    $periodeText = "Sampai: {$filters['endDate']}";
                } else {
                    $periodeText = 'Semua Periode';
                }
                $sheet->setCellValue('A4', $periodeText);
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '9CA3AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5: Divider line
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '2563EB']],
                    ],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(8);

                // Row 6: Empty spacer
                $sheet->getRowDimension(6)->setRowHeight(6);

                // Style data rows (from row 8 onward) with borders
                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
                        ],
                        'font' => ['size' => 9],
                    ]);

                    // Zebra striping on data rows
                    for ($row = 8; $row <= $lastRow; $row++) {
                        if ($row % 2 === 0) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F9FAFB'],
                                ],
                            ]);
                        }
                    }

                    // Nominal column (F) right-aligned
                    $sheet->getStyle("F8:F{$lastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                        'numberFormat' => ['formatCode' => '#,##0'],
                    ]);

                    // Center-align No, Tanggal, Status, Verifikasi columns
                    $sheet->getStyle("A8:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H8:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("I8:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("J8:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Footer row: Print date
                $footerRow = $lastRow + 2;
                $sheet->mergeCells("A{$footerRow}:{$lastCol}{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", 'Dicetak pada: ' . now()->format('d/m/Y H:i:s'));
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
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
            // Check storage path (recommended for uploaded files)
            if (file_exists(storage_path('app/public/' . $this->logo))) {
                $logoPath = storage_path('app/public/' . $this->logo);
            } 
            // Check public storage symlink
            elseif (file_exists(public_path('storage/' . $this->logo))) {
                $logoPath = public_path('storage/' . $this->logo);
            }
            // Check direct public path
            elseif (file_exists(public_path($this->logo))) {
                $logoPath = public_path($this->logo);
            }
        }

        if ($logoPath) {
            $drawing = new Drawing();
            $drawing->setName('Logo Sekolah');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('B1'); // Slightly offset to B1 or keep at A1 but with offset
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawings[] = $drawing;
        }

        return $drawings;
    }
}
