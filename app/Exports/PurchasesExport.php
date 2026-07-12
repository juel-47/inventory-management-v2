<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Purchase::with(['vendor', 'user', 'details'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Vendor',
            'Created By',
            'Date',
            'Total Amount',
            'Paid Amount',
            'Due Amount',
            'Shipping',
            'Status',
            'Payment Status',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->invoice_no,
            $purchase->vendor?->shop_name ?? 'N/A',
            $purchase->user?->name ?? 'System',
            $purchase->date,
            number_format($purchase->total_amount, 2),
            number_format($purchase->paid_amount, 2),
            number_format($purchase->due_amount, 2),
            $purchase->shipping_method ?? 'N/A',
            $purchase->status == 1 ? 'Completed' : 'Draft',
            ucfirst($purchase->payment_status ?? 'Pending'),
        ];
    }
}
