<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class BookingOrderExport implements FromArray, WithCustomStartCell, ShouldAutoSize, WithEvents
{
    protected string $bookingNo;

    public function __construct(string $bookingNo)
    {
        $this->bookingNo = $bookingNo;
    }

    public function array(): array
    {
        return [];
    }

    public function startCell(): string
    {
        return 'Z100';
    }

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
                $sheet = $event->writer->getDelegate()->getActiveSheet();
                $row = 1;

                // ─── TITLE ───
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", 'ORDER PLACE');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(30);
                $row += 2;

                // ─── LEFT: ORDER INFO / RIGHT: VENDOR DETAILS ───
                $infoRow = $row;

                // Left section header
                $sheet->setCellValue("A{$infoRow}", 'ORDER INFORMATION');
                $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells("A{$infoRow}:B{$infoRow}");
                $infoRow++;

                $leftData = [
                    'Order No:' => $this->bookingNo,
                    'Status:' => ucfirst($first->status),
                    'Shipping:' => $first->shipping_method ?? 'N/A',
                ];
                foreach ($leftData as $label => $value) {
                    $sheet->setCellValue("A{$infoRow}", $label);
                    $sheet->getStyle("A{$infoRow}")->getFont()->setBold(true);
                    $sheet->setCellValue("B{$infoRow}", $value);
                    $infoRow++;
                }

                // Right section header
                $rightRow = $row;
                $sheet->setCellValue("D{$rightRow}", 'VENDOR DETAILS');
                $sheet->getStyle("D{$rightRow}")->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells("D{$rightRow}:E{$rightRow}");
                $rightRow++;

                $vendorData = [
                    'Name:' => $vendor?->shop_name ?? 'N/A',
                    'Email:' => $vendor?->email ?? 'N/A',
                    'Phone:' => $vendor?->phone ?? 'N/A',
                    'Address:' => $vendor?->address ?? 'N/A',
                ];
                foreach ($vendorData as $label => $value) {
                    $sheet->setCellValue("D{$rightRow}", $label);
                    $sheet->getStyle("D{$rightRow}")->getFont()->setBold(true);
                    $sheet->setCellValue("E{$rightRow}", $value);
                    $rightRow++;
                }

                $row = max($infoRow, $rightRow) + 1;

                // ─── TABLE ───
                $sheet->setCellValue("A{$row}", 'ORDER DETAILS');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells("A{$row}:E{$row}");
                $row++;

                $headers = ['Image', 'Product Name', 'Product Number', 'Quantity', 'Unit'];
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $row, $header);
                    $sheet->getStyle($col . $row)->getFont()->setBold(true);
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;
                }
                $headerRow = $row;
                $row++;

                $totalQty = 0;
                foreach ($items as $item) {
                    $totalQty += $item->qty;
                    $col = 'A';

                    $imagePath = $item->product?->thumb_image;
                    $fullPath = null;
                    $hasImage = false;

                    if ($imagePath) {
                        $fullPath = public_path($imagePath);
                        if (!file_exists($fullPath)) {
                            $fullPath = storage_path('app/public/' . ltrim($imagePath, '/'));
                        }
                        if (file_exists($fullPath)) {
                            $hasImage = true;
                            $drawing = new Drawing();
                            $drawing->setPath($fullPath);
                            $drawing->setHeight(50);
                            $drawing->setCoordinates('A' . $row);
                            $drawing->setOffsetX(3);
                            $drawing->setOffsetY(3);
                            $drawing->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(55);
                        }
                    }
                    $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col = 'B';

                    $sheet->setCellValue($col . $row, $item->product?->name ?? 'N/A');
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;

                    $sheet->setCellValue($col . $row, $item->product?->product_number ?? 'N/A');
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;

                    $sheet->setCellValue($col . $row, $item->qty);
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $col++;

                    $sheet->setCellValue($col . $row, $item->unit?->name ?? 'N/A');
                    $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($col . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                    if (!$hasImage) {
                        $sheet->getRowDimension($row)->setRowHeight(18);
                    }
                    $row++;
                }

                // ─── GRAND TOTAL ───
                $sheet->setCellValue('A' . $row, '');
                $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue('B' . $row, '');
                $sheet->getStyle('B' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue('C' . $row, 'Grand Total');
                $sheet->getStyle('C' . $row)->getFont()->setBold(true);
                $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue('D' . $row, $totalQty);
                $sheet->getStyle('D' . $row)->getFont()->setBold(true);
                $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->setCellValue('E' . $row, '');
                $sheet->getStyle('E' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $row++;

                for ($r = $headerRow; $r < $row; $r++) {
                    for ($c = 'A'; $c <= 'E'; $c++) {
                        $sheet->getStyle($c . $r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }
            },
        ];
    }
}
