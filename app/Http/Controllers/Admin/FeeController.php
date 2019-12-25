<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\FeesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Fee;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }
    public function index()
    {
        $data['menu'] = 'fee';
        $data['fees'] = $fees = Fee::orderBy('id', 'desc')->get();
        return view('admin.fees.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']        = 'fee';
            $data['payment_met'] = $this->helper->key_value('id', 'name', PaymentMethod::get()->toArray());
            return view('admin.fees.add', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());

            $this->validate($request, [
                'charge_percentage' => 'required|numeric',
                'charge_fixed'      => 'required|numeric',
                'payment_met'       => 'unique_transaction_type',
            ], [
                'payment_met.unique_transaction_type' => 'Each Payment Method can only have unique transaction type',
            ]);

            $fees                    = new Fee();
            $fees->transaction_type  = $request->transaction_type;
            $fees->charge_percentage = $request->charge_percentage;
            $fees->charge_fixed      = $request->charge_fixed;

            foreach ($request->payment_met as $key => $value)
            {
                $fees->payment_method_id = $value;
            }
            // dd($fees);

            $fees->save();

            $this->helper->one_time_message('success', 'Fees Added Successfully');
            return redirect('admin/settings/fees');
        }
    }

    public function update(Request $request, $id)
    {
        $data['transaction_type'] = $transaction_type = 'all';
        $data['payment_method']   = $payment_method   = 'all';

        if (!$_POST)
        {
            $data['menu'] = 'fee';

            $data['result'] = $result = Fee::find($request->id);
            // dd($result);

            $data['payment_met'] = $payment_met = PaymentMethod::pluck('name', 'id')->toArray();
            // dd($payment_met);

            return view('admin.fees.edit', $data);
        }
        else if ($_POST)
        {
            $this->validate($request, [
                'charge_percentage' => 'required|numeric',
                'charge_fixed'      => 'required|numeric',
                'payment_met'       => 'unique_transaction_type',
            ], [
                'payment_met.unique_transaction_type' => 'Each payment method can only have unique transaction type',
            ]);

            $fees                    = Fee::find($request->id);
            $fees->transaction_type  = $request->transaction_type;
            $fees->charge_percentage = $request->charge_percentage;
            $fees->charge_fixed      = $request->charge_fixed;

            foreach ($request->payment_met as $key => $value)
            {
                // dd($value);
                $fees->payment_method_id = $value;
            }
            $fees->save();
            // dd($request->all());
            $this->helper->one_time_message('success', 'Fees Updated Successfully');
            return redirect('admin/settings/fees');
        }
    }

    public function delete(Request $request)
    {
        $fee = Fee::find($request->id);

        if (isset($fee))
        {
            $fee->delete();
        }
        $this->helper->one_time_message('success', 'Fees Deleted Successfully');
        return redirect('admin/settings/fees');
    }
}
