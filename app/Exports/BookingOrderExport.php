<?php

namespace App\Exports;

use App\Models\Booking;
use App\Models\GeneralSetting;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BookingOrderExport implements FromArray, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected string $bookingNo;

    private array $darkHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];

    private array $sectionHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF2FA']],
        'font' => ['bold' => true, 'color' => ['rgb' => '1F3864'], 'size' => 10.5],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
    ];

    private array $tableHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1A5FA3']]],
    ];

    private array $labelStyle = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
        'font' => ['bold' => true, 'size' => 9.5, 'color' => ['rgb' => '475569']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
    ];

    private array $valueStyle = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
        'font' => ['size' => 9.5, 'color' => ['rgb' => '1E293B']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
    ];

    private array $cellBorder = [
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]]
    ];

    public function __construct(string $bookingNo)
    {
        $this->bookingNo = $bookingNo;
    }

    public function array(): array { return []; }
    public function startCell(): string { return 'Z100'; }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $items = Booking::where('booking_no', $this->bookingNo)
                    ->with(['product', 'vendor', 'unit'])
                    ->get();
                if ($items->isEmpty()) return;

                $first = $items->first();
                $vendor = $first->vendor;
                $settings = GeneralSetting::first();
                $sheet = $event->writer->getDelegate()->getActiveSheet();
                $sheet->setTitle('Order Details');

                // ── 1. Column widths ──
                $sheet->getColumnDimension('A')->setWidth(14);
                $sheet->getColumnDimension('B')->setWidth(30);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(12);

                $row = 1;

                // ── 2. SITE / COMPANY HEADER SECTION (Left: Logo | Right: Site Info) ──
                $sheet->mergeCells("A1:C3");
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(8); // Blank spacing row

                $logoPath = null;
                if (!empty($settings?->site_logo)) {
                    $p = public_path($settings->site_logo);
                    if (!file_exists($p)) {
                        $p = storage_path('app/public/' . ltrim($settings->site_logo, '/'));
                    }
                    if (file_exists($p)) {
                        $logoPath = $p;
                    }
                }
                if (!$logoPath && file_exists(public_path('uploads/logo.png'))) {
                    $logoPath = public_path('uploads/logo.png');
                }

                if ($logoPath) {
                    $dwg = new Drawing();
                    $dwg->setPath($logoPath);
                    $dwg->setHeight(48);
                    $dwg->setCoordinates('A1');
                    $dwg->setOffsetX(4);
                    $dwg->setOffsetY(4);
                    $dwg->setWorksheet($sheet);
                }

                // Right Side: Company Details (D1:F3)
                // Row 1: Site / Company Name
                $sheet->mergeCells("D1:F1");
                $sheet->setCellValue("D1", strtoupper($settings->site_name ?? 'B2BVIKING'));
                $sheet->getStyle("D1")->getFont()->setBold(true)->setSize(12.5)->getColor()->setARGB('FF1F3864');
                $sheet->getStyle("D1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // Row 2: Email & Phone
                $sheet->mergeCells("D2:F2");
                $contactInfo = [];
                if (!empty($settings?->contact_email)) {
                    $contactInfo[] = 'Email: ' . $settings->contact_email;
                }
                if (!empty($settings?->phone)) {
                    $contactInfo[] = 'Phone: ' . $settings->phone;
                }
                $sheet->setCellValue("D2", !empty($contactInfo) ? implode('   |   ', $contactInfo) : '');
                $sheet->getStyle("D2")->getFont()->setSize(9.5)->getColor()->setARGB('FF475569');
                $sheet->getStyle("D2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                // Row 3: Address
                $sheet->mergeCells("D3:F3");
                $sheet->setCellValue("D3", !empty($settings?->address) ? ('Address: ' . $settings->address) : '');
                $sheet->getStyle("D3")->getFont()->setSize(9)->getColor()->setARGB('FF64748B');
                $sheet->getStyle("D3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

                $row = 5;

                // ── 3. ORDER PLACE HEADER BAR ──
                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'ORDER PLACE');
                $sheet->getStyle("A{$row}")->applyFromArray($this->darkHeader);
                $sheet->getRowDimension($row)->setRowHeight(30);
                $sheet->getRowDimension($row + 1)->setRowHeight(8); // Blank spacing row
                $row += 2;

                // ── 4. INFO SECTION (Left: A:C | Right: D:F) ──
                $infoRow = $row;
                $sheet->setCellValue("A{$infoRow}", '  ORDER INFORMATION');
                $sheet->getStyle("A{$infoRow}:C{$infoRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$infoRow}:C{$infoRow}");
                $sheet->getRowDimension($infoRow)->setRowHeight(24);
                $infoRow++;

                $leftInfo = [
                    ['Order No:', $this->bookingNo, true],
                    ['Date:', $first->created_at ? $first->created_at->format('d M, Y h:i A') : 'N/A', false],
                    ['Status:', ucfirst($first->status), false],
                    ['Shipping:', $first->shipping_method ?? 'N/A', false],
                ];
                foreach ($leftInfo as $d) {
                    $sheet->setCellValue("A{$infoRow}", $d[0]);
                    $sheet->getStyle("A{$infoRow}")->applyFromArray($this->labelStyle);
                    
                    $sheet->mergeCells("B{$infoRow}:C{$infoRow}");
                    $sheet->setCellValue("B{$infoRow}", $d[1]);
                    $sheet->getStyle("B{$infoRow}:C{$infoRow}")->applyFromArray($this->valueStyle);
                    if ($d[2]) {
                        $sheet->getStyle("B{$infoRow}")->getFont()->setBold(true)->getColor()->setARGB('FF1F3864');
                    }
                    $sheet->getRowDimension($infoRow)->setRowHeight(20);
                    $infoRow++;
                }

                $rightRow = $row;
                $sheet->setCellValue("D{$rightRow}", '  VENDOR DETAILS');
                $sheet->getStyle("D{$rightRow}:F{$rightRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("D{$rightRow}:F{$rightRow}");
                $sheet->getRowDimension($rightRow)->setRowHeight(24);
                $rightRow++;

                $vData = [
                    ['Vendor Name:', $vendor?->shop_name ?? 'N/A', true],
                    ['Email:', $vendor?->email ?? 'N/A', false],
                    ['Phone:', $vendor?->phone ?? 'N/A', false],
                    ['Address:', $vendor?->address ?? 'N/A', false],
                ];
                foreach ($vData as $d) {
                    $sheet->setCellValue("D{$rightRow}", $d[0]);
                    $sheet->getStyle("D{$rightRow}")->applyFromArray($this->labelStyle);

                    $sheet->mergeCells("E{$rightRow}:F{$rightRow}");
                    $sheet->setCellValue("E{$rightRow}", $d[1]);
                    $sheet->getStyle("E{$rightRow}:F{$rightRow}")->applyFromArray($this->valueStyle);
                    if ($d[2]) {
                        $sheet->getStyle("E{$rightRow}")->getFont()->setBold(true);
                    }
                    $sheet->getRowDimension($rightRow)->setRowHeight(20);
                    $rightRow++;
                }

                $row = max($infoRow, $rightRow);
                $sheet->getRowDimension($row)->setRowHeight(8);
                $row++;

                // ── 5. PRODUCT TABLE ──
                $sheet->setCellValue("A{$row}", '  ORDER DETAILS');
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->getRowDimension($row)->setRowHeight(24);
                $row++;

                $headers = ['Image', 'Product Name', 'Product Number', 'Variants', 'Quantity', 'Unit'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->applyFromArray($this->tableHeader);
                    $col++;
                }
                $sheet->getRowDimension($row)->setRowHeight(26);
                $headerRow = $row;
                $row++;

                $evenRow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']]];
                $totalQty = 0;
                $isEven = false;

                foreach ($items as $item) {
                    $totalQty += $item->qty;
                    $hasImage = false;

                    $imagePath = $item->product?->thumb_image;
                    if ($imagePath) {
                        $fp = public_path($imagePath);
                        if (!file_exists($fp)) {
                            $fp = storage_path('app/public/' . ltrim($imagePath, '/'));
                        }
                        if (file_exists($fp)) {
                            $hasImage = true;
                            $dwg = new Drawing();
                            $dwg->setPath($fp);
                            $dwg->setHeight(46);
                            $dwg->setCoordinates('A' . $row);
                            $dwg->setOffsetX(4);
                            $dwg->setOffsetY(3);
                            $dwg->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(52);
                        }
                    }
                    $sheet->getStyle('A' . $row)->applyFromArray($this->cellBorder);
                    if ($isEven) $sheet->getStyle('A' . $row)->applyFromArray($evenRow);

                    $variantStr = '';
                    if ($item->variant_info) {
                        $parts = [];
                        foreach ($item->variant_info as $vName => $vQty) {
                            $parts[] = $vName . ': ' . $vQty;
                        }
                        $variantStr = implode(', ', $parts);
                    }
                    $data = [
                        $item->product?->name ?? 'N/A',
                        $item->product?->product_number ?? 'N/A',
                        $variantStr ?: '—',
                        $item->qty,
                        $item->unit?->name ?? 'N/A',
                    ];
                    $alignments = [
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_LEFT,
                        Alignment::HORIZONTAL_CENTER,
                        Alignment::HORIZONTAL_CENTER,
                    ];
                    $col = 'B';
                    foreach ($data as $idx => $val) {
                        $sheet->setCellValue($col . $row, $val);
                        $sheet->getStyle($col . $row)->applyFromArray($this->cellBorder);
                        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal($alignments[$idx]);
                        $sheet->getStyle($col . $row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        if ($idx === 1) { // Product Number
                            $sheet->getStyle($col . $row)->getFont()->setBold(true)->getColor()->setARGB('FF1F3864');
                        }
                        if ($idx === 3) { // Quantity
                            $sheet->getStyle($col . $row)->getFont()->setBold(true);
                        }
                        if ($isEven) $sheet->getStyle($col . $row)->applyFromArray($evenRow);
                        $col++;
                    }

                    if (!$hasImage) $sheet->getRowDimension($row)->setRowHeight(24);
                    $isEven = !$isEven;
                    $row++;
                }

                // ── 6. GRAND TOTAL ──
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue("A{$row}", 'Grand Total:');
                $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF2FA']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1F3864'], 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                $sheet->setCellValue("E{$row}", $totalQty);
                $sheet->getStyle("E{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF2FA']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1F3864'], 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                $sheet->setCellValue("F{$row}", '');
                $sheet->getStyle("F{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF2FA']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(25);
            },
        ];
    }
}
