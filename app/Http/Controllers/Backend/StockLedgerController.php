<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockLedger::with(['product', 'variant'])->select('stock_ledgers.*');

            if ($request->filled('product_id')) {
                $data->where('product_id', $request->integer('product_id'));
            }

            if ($request->filled('variant_id')) {
                $data->where('variant_id', $request->integer('variant_id'));
            }

            if ($request->filled('reference_type')) {
                $data->where('reference_type', $request->string('reference_type')->toString());
            }

            if ($request->filled('movement_type')) {
                $movementType = $request->string('movement_type')->toString();

                if ($movementType === 'in') {
                    $data->where('in_qty', '>', 0);
                }

                if ($movementType === 'out') {
                    $data->where('out_qty', '>', 0);
                }
            }

            if ($request->filled('date_from')) {
                $data->whereDate('created_at', '>=', $request->date('date_from')->toDateString());
            }

            if ($request->filled('date_to')) {
                $data->whereDate('created_at', '<=', $request->date('date_to')->toDateString());
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->created_at)->format('Y-m-d h:i A');
                })
                ->addColumn('image', function ($row) {
                    $url = $row->product && $row->product->thumb_image ? asset('storage/'.$row->product->thumb_image) : asset('uploads/default.jpg');

                    return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product->name ?? 'Deleted';
                })
                ->filterColumn('product_name', function ($query, $keyword) {
                    $query->whereHas('product', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('variant_name', function ($row) {
                    return $row->variant ? $row->variant->name : '-';
                })
                ->filterColumn('variant_name', function ($query, $keyword) {
                    $query->whereHas('variant', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('reference', function ($row) {
                    return $row->reference_type.' #'.$row->reference_id;
                })
                ->filterColumn('reference', function ($query, $keyword) {
                    $query->where('reference_id', 'like', "%{$keyword}%")
                        ->orWhere('reference_type', 'like', "%{$keyword}%");
                })
                ->addColumn('type', function ($row) {
                    if ($row->in_qty > 0) {
                        return '<div class="badge badge-success">IN</div>';
                    } else {
                        return '<div class="badge badge-danger">OUT</div>';
                    }
                })
                ->rawColumns(['image', 'type'])
                ->make(true);
        }

        $products = Product::query()
            ->select('products.id', 'products.name')
            ->whereIn('products.id', StockLedger::query()->select('product_id')->whereNotNull('product_id')->distinct())
            ->with(['variants' => function ($query) {
                $query->select('id', 'product_id', 'name', 'color', 'size')
                    ->whereIn('id', StockLedger::query()->select('variant_id')->whereNotNull('variant_id')->distinct());
            }])
            ->orderByDesc('products.id')
            ->get();

        $ledgerProducts = $products->mapWithKeys(function ($product) {
            return [
                (string) $product->id => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'label' => $variant->name ?: trim(collect([$variant->color, $variant->size])->filter()->implode(' ')) ?: 'Variant #'.$variant->id,
                    ];
                })->values()->all(),
            ];
        })->toArray();

        $referenceTypes = StockLedger::query()
            ->whereNotNull('reference_type')
            ->distinct()
            ->orderBy('reference_type')
            ->pluck('reference_type');

        return view('backend.stock_ledger.index', compact('products', 'ledgerProducts', 'referenceTypes'));
    }
}
