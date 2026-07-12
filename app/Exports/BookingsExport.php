<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Booking::with(['vendor', 'product', 'unit'])
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('booking_no');
    }

    public function headings(): array
    {
        return [
            'Booking No',
            'Vendor',
            'Products',
            'Total Qty',
            'Shipping Method',
            'Status',
            'Date',
        ];
    }

    public function map($group): array
    {
        $first = $group->first();
        return [
            $group->first()->booking_no,
            $first->vendor?->shop_name ?? 'N/A',
            $group->count() . ' Items',
            $group->sum('qty'),
            $first->shipping_method ?? 'N/A',
            ucfirst($first->status),
            $first->created_at->format('Y-m-d'),
        ];
    }
}
