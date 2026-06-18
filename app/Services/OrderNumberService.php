<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    public static function generate(string $prefix, string $modelClass, ?string $sequenceKey = null): string
    {
        $dateSuffix = now()->format('mY');
        $key = $sequenceKey ? $sequenceKey . '_' . $dateSuffix : $dateSuffix;

        return DB::transaction(function () use ($prefix, $dateSuffix, $key, $sequenceKey) {
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->lockForUpdate()
                ->first();

            $current = DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->value('current_serial');

            if ($current === null) {
                $maxSerial = 0;

                if ($sequenceKey) {
                    // Separate sequence — check bookings table
                    $rows = DB::table('bookings')
                        ->where('booking_no', 'LIKE', $prefix . '-_____-' . $dateSuffix)
                        ->orderBy('booking_no', 'desc')
                        ->get(['booking_no']);
                    foreach ($rows as $row) {
                        $parts = explode('-', $row->booking_no);
                        $sn = (int) ($parts[count($parts) - 2] ?? 0);
                        if ($sn > $maxSerial) $maxSerial = $sn;
                    }
                } else {
                    // Shared sequence — check orders + product_requests
                    foreach (['orders' => 'order_no', 'product_requests' => 'request_no'] as $table => $col) {
                        $rows = DB::table($table)
                            ->where($col, 'LIKE', '%-_____-' . $dateSuffix)
                            ->orderBy($col, 'desc')
                            ->get([$col]);
                        foreach ($rows as $row) {
                            $parts = explode('-', $row->$col);
                            $sn = (int) ($parts[count($parts) - 2] ?? 0);
                            if ($sn > $maxSerial) $maxSerial = $sn;
                        }
                    }
                }

                DB::table('order_sequences')->insert([
                    'prefix_month' => $key,
                    'current_serial' => $maxSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current = $maxSerial;
            }

            $next = $current + 1;
            DB::table('order_sequences')
                ->where('prefix_month', $key)
                ->update(['current_serial' => $next, 'updated_at' => now()]);

            $serial = str_pad($next, 5, '0', STR_PAD_LEFT);
            return "{$prefix}-{$serial}-{$dateSuffix}";
        });
    }
}
