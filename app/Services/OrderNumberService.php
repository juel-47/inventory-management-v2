<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OrderNumberService
{
    public static function generate(string $prefix, string $modelClass): string
    {
        $dateSuffix = now()->format('mY');

        return DB::transaction(function () use ($prefix, $dateSuffix) {
            // Single shared counter for all prefixes — key: month only
            DB::table('order_sequences')
                ->where('prefix_month', $dateSuffix)
                ->lockForUpdate()
                ->first();

            $current = DB::table('order_sequences')
                ->where('prefix_month', $dateSuffix)
                ->value('current_serial');

            if ($current === null) {
                // First use this month — find highest serial across ALL prefixes
                $maxSerial = 0;
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

                DB::table('order_sequences')->insert([
                    'prefix_month' => $dateSuffix,
                    'current_serial' => $maxSerial,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $current = $maxSerial;
            }

            $next = $current + 1;
            DB::table('order_sequences')
                ->where('prefix_month', $dateSuffix)
                ->update(['current_serial' => $next, 'updated_at' => now()]);

            $serial = str_pad($next, 5, '0', STR_PAD_LEFT);
            return "{$prefix}-{$serial}-{$dateSuffix}";
        });
    }
}
