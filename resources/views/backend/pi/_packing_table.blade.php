@php
    $piType = $piInfo['pi_type'] ?? 'simple';
    $rows = $piInfo['rows'] ?? [];
    $blocks = $piInfo['blocks'] ?? [];
@endphp

<div style="margin-top: 28px;">
    @if($piType === 'advanced')
        <table style="margin-bottom: 18px; border: 1px solid #222; font-size: 13px;">
            <tbody>
                <tr>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 18%; font-weight: bold;">SHIPMENT QTY :</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 32%; font-weight: bold;">{{ number_format($piInfo['shipment_qty'] ?? 0) }} PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 18%; font-weight: bold;">DATE :</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; font-weight: bold;">
                        {{ !empty($piInfo['shipment_date']) ? \Illuminate\Support\Carbon::parse($piInfo['shipment_date'])->format('d-M-Y') : 'N/A' }}
                    </td>
                </tr>
            </tbody>
        </table>

        @foreach($blocks as $block)
            @php
                $variantHeaders = $block['variant_headers'] ?? [];
                $rowsToShow = collect($block['rows'] ?? [])->filter(function ($row) {
                    return ($row['ctn_qty'] ?? null) !== null
                        || !empty($row['ctn_no'])
                        || collect($row['variants'] ?? [])->contains(fn ($value) => $value !== null && $value !== '')
                        || ($row['pcs'] ?? null) !== null
                        || ($row['total_pcs'] ?? null) !== null
                        || ($row['nw_kg'] ?? null) !== null
                        || ($row['gw_kg'] ?? null) !== null;
                })->values();
                $blockCtn = $rowsToShow->sum(fn ($row) => max(0, (int) ($row['ctn_qty'] ?? 0)));
                $blockTotalPcs = $rowsToShow->sum(fn ($row) => max(0, (int) ($row['total_pcs'] ?? \App\Support\PiInfoSupport::rowPcs($row))));
                $blockNw = $rowsToShow->sum(fn ($row) => max(0, (float) ($row['nw_kg'] ?? 0)));
                $blockGw = $rowsToShow->sum(fn ($row) => max(0, (float) ($row['gw_kg'] ?? 0)));
                $activeVariantIndexes = collect($variantHeaders)->keys()->filter(function ($index) use ($rowsToShow) {
                    return $rowsToShow->sum(fn ($row) => max(0, (int) ($row['variants'][$index] ?? 0))) > 0;
                })->values()->all();
                $activeVariantMap = array_map(fn ($index) => $variantHeaders[$index], $activeVariantIndexes);
                $variantHeaderCount = count($activeVariantMap);
                $imagePath = (string) ($block['image'] ?? '');
                $imageBase64 = null;
                if ($imagePath !== '') {
                    $normalized = ltrim(str_replace('storage/', '', $imagePath), '/');
                    $candidates = [
                        public_path(ltrim($imagePath, '/')),
                        public_path('storage/' . $normalized),
                        storage_path('app/public/' . $normalized),
                    ];
                    foreach ($candidates as $candidate) {
                        if (is_file($candidate)) {
                            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION) ?: 'jpg');
                            $mime = match ($ext) {
                                'png' => 'png',
                                'gif' => 'gif',
                                'webp' => 'webp',
                                default => 'jpeg',
                            };
                            $imageBase64 = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($candidate));
                            break;
                        }
                    }
                }
            @endphp
            <table style="margin-bottom: 22px; border: 1px solid #222; font-size: 12px;">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 14%; border: 1px solid #222; text-align: center;">COLOR</th>
                        <th rowspan="2" style="width: 12%; border: 1px solid #222; text-align: center;">Picture</th>
                        <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center;">CTN QTY</th>
                        <th rowspan="2" style="width: 10%; border: 1px solid #222; text-align: center;">CTN NO.</th>
                        @if($variantHeaderCount > 0)
                            <th colspan="{{ $variantHeaderCount }}" style="text-align: center; border: 1px solid #222;">VARIANT QTY</th>
                        @endif
                        <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center;">PCS</th>
                        <th rowspan="2" style="width: 10%; border: 1px solid #222; text-align: center;">TOTAL PCS</th>
                        <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center;">N.W(KG)</th>
                        <th rowspan="2" style="width: 8%; border: 1px solid #222; text-align: center;">G.W(KG)</th>
                    </tr>
                    @if($variantHeaderCount > 0)
                        <tr>
                            @foreach($activeVariantMap as $header)
                                <th style="text-align: center; border: 1px solid #222;">{{ $header }}</th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($rowsToShow as $rowIndex => $row)
                        <tr>
                            @if($rowIndex === 0)
                                <td rowspan="{{ $rowsToShow->count() + 1 }}" style="font-weight: bold; vertical-align: middle; border: 1px solid #222; text-align: center;">
                                    <div>{{ $block['color_label'] ?: 'N/A' }}</div>
                                    @if(!empty($block['title']))
                                        <div style="font-size: 10px; color: #444; margin-top: 6px;">{{ $block['title'] }}</div>
                                    @endif
                                </td>
                                <td rowspan="{{ $rowsToShow->count() + 1 }}" style="text-align: center; vertical-align: middle; border: 1px solid #222;">
                                    @if($imageBase64)
                                        <img src="{{ $imageBase64 }}" alt="" style="max-width: 72px; max-height: 72px; object-fit: contain;">
                                    @else
                                        <span style="font-size: 11px; color: #888;">No Image</span>
                                    @endif
                                </td>
                            @endif
                            <td style="border: 1px solid #222; text-align: center;">{{ $row['ctn_qty'] !== null ? number_format((int) $row['ctn_qty']) : '-' }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ $row['ctn_no'] ?: '-' }}</td>
                            @foreach($activeVariantIndexes as $variantIndex)
                                <td style="text-align: center; border: 1px solid #222;">{{ isset($row['variants'][$variantIndex]) && $row['variants'][$variantIndex] !== null ? number_format((int) $row['variants'][$variantIndex]) : '' }}</td>
                            @endforeach
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format(\App\Support\PiInfoSupport::rowPcs($row)) }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ $row['total_pcs'] !== null ? number_format((int) $row['total_pcs']) : number_format(\App\Support\PiInfoSupport::rowPcs($row)) }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ $row['nw_kg'] !== null ? number_format((float) $row['nw_kg'], 2) : '-' }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ $row['gw_kg'] !== null ? number_format((float) $row['gw_kg'], 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 8 + $variantHeaderCount }}" style="text-align: center; color: #777; border: 1px solid #222;">No matrix rows saved yet.</td>
                        </tr>
                    @endforelse
                    @if($rowsToShow->isNotEmpty())
                        <tr style="background: #f3f4f6; font-weight: bold;">
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockCtn) }} CTN</td>
                            <td style="border: 1px solid #222; text-align: center;">TOTAL</td>
                            @foreach($activeVariantIndexes as $variantIndex)
                                <td style="text-align: center; border: 1px solid #222;">
                                    {{ number_format($rowsToShow->sum(fn ($row) => max(0, (int) ($row['variants'][$variantIndex] ?? 0)))) }}
                                </td>
                            @endforeach
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format($rowsToShow->sum(fn ($row) => \App\Support\PiInfoSupport::rowPcs($row))) }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockTotalPcs) }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockNw, 2) }}</td>
                            <td style="border: 1px solid #222; text-align: center;">{{ number_format($blockGw, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @endforeach

        <table style="margin-bottom: 0; border: 1px solid #222; font-size: 12px;">
            <tbody>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">ORDER QTY</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">{{ number_format($piInfo['order_qty_total'] ?? 0) }} PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 10px; width: 25%;">TOTAL CTN</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['ctn_qty'] ?? 0) }}</td>
                </tr>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td style="border: 1px solid #222; padding: 8px 10px;">TOTAL PCS</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['total_pcs'] ?? 0) }}</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">TOTAL N.W / G.W</td>
                    <td style="border: 1px solid #222; padding: 8px 10px;">{{ number_format($piTotals['nw_kg'] ?? 0, 2) }} / {{ number_format($piTotals['gw_kg'] ?? 0, 2) }} KG</td>
                </tr>
            </tbody>
        </table>
    @else
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
            <div style="background: #f8fafc; padding: 14px 18px; border-bottom: 1px solid #e5e7eb;">
                <h3 style="margin: 0; font-size: 18px;">Packing / CTN Summary</h3>
            </div>
            <div style="padding: 18px;">
                <table style="margin: 0;">
                    <tbody>
                        <tr>
                            <td style="border: none; padding: 6px 0; width: 22%;"><strong>Order Qty</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piInfo['order_qty_total'] ?? 0) }} PCS</td>
                            <td style="border: none; padding: 6px 0; width: 22%;"><strong>Shipment Qty</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piInfo['shipment_qty'] ?? 0) }} PCS</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0;"><strong>Shipment Date</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ !empty($piInfo['shipment_date']) ? \Illuminate\Support\Carbon::parse($piInfo['shipment_date'])->format('d-M-Y') : 'N/A' }}</td>
                            <td style="border: none; padding: 6px 0;"><strong>Total CTN</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['ctn_qty'] ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td style="border: none; padding: 6px 0;"><strong>Total PCS</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['total_pcs'] ?? 0) }}</td>
                            <td style="border: none; padding: 6px 0;"><strong>Total N.W / G.W</strong></td>
                            <td style="border: none; padding: 6px 0;">{{ number_format($piTotals['nw_kg'] ?? 0, 2) }} / {{ number_format($piTotals['gw_kg'] ?? 0, 2) }} KG</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 10%;" class="text-right">Ord. Qty</th>
                    <th style="width: 12%;">CTN No</th>
                    <th style="width: 17%;">CTN Size</th>
                    <th style="width: 10%;" class="text-right">PCS/CTN</th>
                    <th style="width: 10%;" class="text-right">CTN Qty</th>
                    <th style="width: 10%;" class="text-right">Total PCS</th>
                    <th style="width: 8%;" class="text-right">N.W</th>
                    <th style="width: 8%;" class="text-right">G.W</th>
                    <th style="width: 17%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-right">{{ isset($row['ordered_qty']) ? number_format((int) $row['ordered_qty']) : '-' }}</td>
                        <td>{{ $row['ctn_no'] ?: '-' }}</td>
                        <td>{{ $row['ctn_size'] ?: '-' }}</td>
                        <td class="text-right">{{ isset($row['pcs_per_ctn']) ? number_format((int) $row['pcs_per_ctn']) : '-' }}</td>
                        <td class="text-right">{{ isset($row['ctn_qty']) ? number_format((int) $row['ctn_qty']) : '-' }}</td>
                        <td class="text-right">{{ isset($row['total_pcs']) ? number_format((int) $row['total_pcs']) : '-' }}</td>
                        <td class="text-right">{{ isset($row['nw_kg']) ? number_format((float) $row['nw_kg'], 2) : '-' }}</td>
                        <td class="text-right">{{ isset($row['gw_kg']) ? number_format((float) $row['gw_kg'], 2) : '-' }}</td>
                        <td>{{ $row['note'] ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #777;">No PI packing rows saved yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(!empty($piInfo['packing_note']))
        <div style="margin-top: 14px; padding: 12px 14px; background: #fff7ed; border-left: 4px solid #fb923c;">
            <strong>Packing Note:</strong> {{ $piInfo['packing_note'] }}
        </div>
    @endif
</div>
