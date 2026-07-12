<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PurchaseOrderExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    protected int $purchaseId;
    protected array $items;

    public function __construct(int $purchaseId)
    {
        $this->purchaseId = $purchaseId;
    }

    public function array(): array
    {
        $purchase = Purchase::with(['vendor', 'details.product'])->findOrFail($this->purchaseId);
        $this->items = $purchase->details->toArray();

        return $purchase->details->map(function ($detail) use ($purchase) {
            return [
                'invoice_no' => $purchase->invoice_no,
                'vendor' => $purchase->vendor?->shop_name ?? 'N/A',
                'name' => $detail->product?->name ?? 'N/A',
                'qty' => $detail->qty,
                'unit_cost' => $detail->unit_cost ?? 0,
                'total' => $detail->total ?? 0,
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'Invoice No', 'Vendor', 'Product Name', 'Image',
            'Quantity', 'Unit Cost', 'Total',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                $purchase = Purchase::with(['details.product'])->findOrFail($this->purchaseId);
                $sheet = $event->getWriter()->getDelegate();
                $row = 2;
                foreach ($purchase->details as $detail) {
                    $imagePath = $detail->product?->thumb_image;
                    if ($imagePath) {
                        $fullPath = public_path($imagePath);
                        if (!file_exists($fullPath)) {
                            $fullPath = storage_path('app/public/' . ltrim($imagePath, '/'));
                        }
                        if (file_exists($fullPath)) {
                            $drawing = new Drawing();
                            $drawing->setPath($fullPath);
                            $drawing->setHeight(50);
                            $drawing->setCoordinates('D' . $row);
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawing->setWorksheet($sheet);
                            $sheet->getRowDimension($row)->setRowHeight(55);
                        }
                    }
                    $row++;
                }
            },
        ];
    }
}
