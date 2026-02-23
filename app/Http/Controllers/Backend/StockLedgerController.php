<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLedger;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockLedger::with(['product', 'variant'])->select('stock_ledgers.*');
            return \Yajra\DataTables\Facades\DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function($row){
                    return \Carbon\Carbon::parse($row->created_at)->format('Y-m-d h:i A');
                })
                ->addColumn('image', function($row){
                    $url = $row->product && $row->product->thumb_image ? asset('storage/'.$row->product->thumb_image) : asset('uploads/default.jpg');
                    return '<img src="'.$url.'" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
                })
                ->addColumn('product_name', function($row){
                    return $row->product->name ?? 'Deleted';
                })
                ->filterColumn('product_name', function($query, $keyword) {
                    $query->whereHas('product', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('variant_name', function($row){
                    return $row->variant ? $row->variant->name : '-';
                })
                ->filterColumn('variant_name', function($query, $keyword) {
                    $query->whereHas('variant', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('reference', function($row){
                    return $row->reference_type . ' #' . $row->reference_id;
                })
                ->filterColumn('reference', function($query, $keyword) {
                    $query->where('reference_id', 'like', "%{$keyword}%")
                          ->orWhere('reference_type', 'like', "%{$keyword}%");
                })
                ->addColumn('type', function($row){
                    if($row->in_qty > 0)
                        return '<div class="badge badge-success">IN</div>';
                    else
                        return '<div class="badge badge-danger">OUT</div>';
                })
                ->rawColumns(['image', 'type'])
                ->make(true);
        }
            
        return view('backend.stock_ledger.index');
    }
}
