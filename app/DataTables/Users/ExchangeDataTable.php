<?php

namespace App\DataTables\Users;
use App\Models\CurrencyExchange;
use App\User;
use Yajra\DataTables\Services\DataTable;
use Auth;

class ExchangeDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @return \Yajra\Datatables\Engines\BaseEngine
     */
    public function ajax() //don't use default dataTable() method
    {
        $exchange = $this->query();

        return datatables()
            ->of($exchange)
            ->addColumn('action', function ($exchange) {
                $view = '<a href="'.url('exchange/view/'.$exchange->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-eye-open"></i></a>&nbsp;';
                return $view;
            })
            ->addColumn('code')
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        
        $exchange = CurrencyExchange::where(['user_id'=>Auth::user()->id])
                        ->join('currencies', function($join) {
                                $join->on('currencies.id', '=', 'currency_exchanges.currency_id');
                            })
                        ->select('currency_exchanges.*','currencies.code');

        return $this->applyScopes($exchange);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'code', 'name' => 'currencies.code', 'title' => 'Exchange From'])
            ->addColumn(['data' => 'exchange_rate', 'name' => 'currency_exchanges.exchange_rate', 'title' => 'Exchange Rate'])
            ->addColumn(['data' => 'amount', 'name' => 'currency_exchanges.amount', 'title' => 'Amount'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false,'width'=>'5%'])
            ->parameters($this->getBuilderParameters());
    }

}
