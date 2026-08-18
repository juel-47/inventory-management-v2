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
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
        'font' => ['bold' => true, 'color' => ['rgb' => '1F3864'], 'size' => 11],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ];
    private array $tableHeader = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E75B6']],
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];
    private array $cellBorder = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

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
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(32);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(12);

                $row = 1;

                // ── 2. SITE / COMPANY HEADER SECTION ──
                $sheet->mergeCells("A1:A3");
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);

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
                    $dwg->setHeight(55);
                    $dwg->setCoordinates('A1');
                    $dwg->setOffsetX(8);
                    $dwg->setOffsetY(4);
                    $dwg->setWorksheet($sheet);
                }

                // Company Name
                $sheet->mergeCells("B1:F1");
                $sheet->setCellValue("B1", strtoupper($settings->site_name ?? 'b2bviking'));
                $sheet->getStyle("B1")->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1F3864');
                $sheet->getStyle("B1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Company Contact (Email & Phone)
                $sheet->mergeCells("B2:F2");
                $contactInfo = [];
                if (!empty($settings?->contact_email)) {
                    $contactInfo[] = 'Email: ' . $settings->contact_email;
                }
                if (!empty($settings?->phone)) {
                    $contactInfo[] = 'Phone: ' . $settings->phone;
                }
                $sheet->setCellValue("B2", !empty($contactInfo) ? implode('   |   ', $contactInfo) : '');
                $sheet->getStyle("B2")->getFont()->setSize(10)->getColor()->setARGB('FF555555');
                $sheet->getStyle("B2")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Company Address
                $sheet->mergeCells("B3:F3");
                $sheet->setCellValue("B3", !empty($settings?->address) ? ('Address: ' . $settings->address) : '');
                $sheet->getStyle("B3")->getFont()->setSize(9)->getColor()->setARGB('FF777777');
                $sheet->getStyle("B3")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $row = 5;

                // ── 3. ORDER PLACE HEADER BAR ──
                $sheet->mergeCells("A{$row}:F{$row}");
                $sheet->setCellValue("A{$row}", 'ORDER PLACE');
                $sheet->getStyle("A{$row}")->applyFromArray($this->darkHeader);
                $sheet->getRowDimension($row)->setRowHeight(30);
                $row += 2;

                // ── 4. INFO SECTION (Left: Order Info / Right: Vendor Details) ──
                $infoRow = $row;
                $sheet->setCellValue("A{$infoRow}", 'ORDER INFORMATION');
                $sheet->getStyle("A{$infoRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$infoRow}:B{$infoRow}");
                $infoRow++;
                $leftInfo = [
                    ['Order No:', $this->bookingNo],
                    ['Date:', $first->created_at ? $first->created_at->format('d M, Y h:i A') : 'N/A'],
                    ['Status:', ucfirst($first->status)],
                    ['Shipping:', $first->shipping_method ?? 'N/A'],
                ];
                foreach ($leftInfo as $d) {
                    $sheet->setCellValue("A{$infoRow}", $d[0]);
                    $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$infoRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->setCellValue("B{$infoRow}", $d[1]);
                    $infoRow++;
                }

                $rightRow = $row;
                $sheet->setCellValue("D{$rightRow}", 'VENDOR DETAILS');
                $sheet->getStyle("D{$rightRow}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("D{$rightRow}:F{$rightRow}");
                $rightRow++;
                $vData = [
                    ['Name:', $vendor?->shop_name ?? 'N/A'],
                    ['Email:', $vendor?->email ?? 'N/A'],
                    ['Phone:', $vendor?->phone ?? 'N/A'],
                    ['Address:', $vendor?->address ?? 'N/A'],
                ];
                foreach ($vData as $d) {
                    $sheet->setCellValue("D{$rightRow}", $d[0]);
                    $sheet->getStyle("D{$rightRow}")->getFont()->setBold(true);
                    $sheet->getStyle("D{$rightRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->mergeCells("E{$rightRow}:F{$rightRow}");
                    $sheet->setCellValue("E{$rightRow}", $d[1]);
                    $rightRow++;
                }

                $row = max($infoRow, $rightRow) + 1;

                // ── 5. PRODUCT TABLE ──
                $sheet->setCellValue("A{$row}", 'ORDER DETAILS');
                $sheet->getStyle("A{$row}")->applyFromArray($this->sectionHeader);
                $sheet->mergeCells("A{$row}:F{$row}");
                $row++;

                $headers = ['Image', 'Product Name', 'Product Number', 'Variants', 'Quantity', 'Unit'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->applyFromArray($this->tableHeader);
                    $col++;
                }
                $headerRow = $row;
                $row++;

                $evenRow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']]];
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
                            $dwg->setHeight(45);
                            $dwg->setCoordinates('A' . $row);
                            $dwg->setOffsetX(3);
                            $dwg->setOffsetY(3);
                            $dwg->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(50);
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
                        if ($isEven) $sheet->getStyle($col . $row)->applyFromArray($evenRow);
                        $col++;
                    }

                    if (!$hasImage) $sheet->getRowDimension($row)->setRowHeight(22);
                    $isEven = !$isEven;
                    $row++;
                }

                // ── 6. GRAND TOTAL ──
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("D{$row}", 'Grand Total');
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("D{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("D{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->setCellValue("E{$row}", $totalQty);
                $sheet->getStyle("E{$row}")->getFont()->setBold(true);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->setCellValue("F{$row}", '');
                $sheet->getStyle("F{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $sheet->getStyle("A{$row}:C{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$row}:C{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('D6E4F0');
                $row++;

                // Borders for table
                for ($r = $headerRow; $r < $row; $r++) {
                    for ($c = 'A'; $c <= 'F'; $c++) {
                        $sheet->getStyle($c . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }
            },
        ];
    }
}
