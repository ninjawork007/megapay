<?php

namespace App\Http\Controllers\Api;

use App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Models\Preference;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $email;

    public function __construct()
    {
        $this->email = new EmailController();
    }

    public function getTransactionApi()
    {
        // dd(request()->all());

        if (request('type') && request('user_id'))
        {
            $type    = request('type');
            $user_id = request('user_id');

            $transaction  = new Transaction();
            $transactions = $transaction->getTransactionLists($type, $user_id);

            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success, 'transactions' => $transactions], $this->successStatus);
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }

    public function getTransactionDetailsApi()
    {
        // dd(request()->all());
        if (request('user_id'))
        {
            $user_id           = request('user_id');
            $tr_id             = request('tr_id');
            $transaction       = new Transaction();
            $transaction       = $transaction->getTransactionDetails($tr_id, $user_id);
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success, 'transaction' => $transaction], $this->successStatus);
        }
        else
        {
            echo "In else block";exit();return false;
        }
    }
}
