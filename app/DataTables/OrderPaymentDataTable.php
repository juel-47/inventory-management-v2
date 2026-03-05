<?php

namespace App\DataTables;

use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<OrderPayment> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('order_link', function ($query) {
                return '<a href="' . route('admin.orders.show', $query->order_id) . '"><strong>' . e($query->order->order_no) . '</strong></a>';
            })
            ->addColumn('customer', function ($query) {
                return e($query->order->billing_name);
            })
            ->editColumn('payment_method', function ($query) {
                return '<span class="badge badge-info">' . strtoupper(e($query->payment_method)) . '</span>';
            })
            ->editColumn('amount', function ($query) {
                return number_format($query->amount, 2);
            })
            ->editColumn('created_at', function ($query) {
                return $query->created_at->format('d M, Y h:i A');
            })
            ->rawColumns(['order_link', 'payment_method'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<OrderPayment>
     */
    public function query(OrderPayment $model): QueryBuilder
    {
        $query = $model->newQuery()->with('order');

        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request()->start_date);
        }

        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request()->end_date);
        }

        if (request()->filled('method')) {
            $query->where('payment_method', request()->method);
        }

        return $query->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-payment-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->width(50),
            Column::make('created_at')->title('Date'),
            Column::computed('order_link')->title('Order No'),
            Column::computed('customer')->title('Customer'),
            Column::computed('payment_method')->title('Method'),
            Column::make('transaction_id')->title('Trans ID'),
            Column::make('amount')->title('Amount'),
            Column::make('note')->title('Note'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Payments_' . date('YmdHis');
    }
}
