<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Common;
use App\Models\AppToken;
use App\Models\AppTransactionsInfo;
use App\Models\Currency;
use App\Models\MerchantApp;
use App\Models\MerchantPayment;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MerchantApiPayment extends Controller
{
    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }

    public function verifyClient(Request $request)
    {
        $app      = $this->verifyClientIdAndClientSecret($request->client_id, $request->client_secret);
        $response = $this->createAccessToken($app); //will expire in one hour
        return json_encode($response);
    }
	
	public function verifyClients(Request $request)//my demo 2019-11-30
    {
    	
		$arrayA = array();
    	foreach($request->merchants as $key => $value) {
        	
    		$app      = $this->verifyClientIdAndClientSecret($key, $value);
    		$response = $this->createAccessToken($app); //will expire in one hour
    		array_push($arrayA, $response);
    	}
		
        return $arrayA;
    }

    protected function verifyClientIdAndClientSecret($client_id, $client_secret)
    {
        $app = MerchantApp::where(['client_id' => $client_id, 'client_secret' => $client_secret])->first();
        if (!$app)
        {
            $res = [
                'status'  => 'error',
                'message' => 'Can not verify the client. Please check client Id and Client Secret',
                'data'    => [],
            ];
            return json_encode($res);
        }
        return $app;
    }

    protected function createAccessToken($app)
    {
        $token = $app->accessToken()->create(['token' => str_random(26), 'expires_in' => time() + 3600]);
        $res   = [
            'status'  => 'success',
            'message' => 'Client Verified',
            'data'    => [
                'access_token' => $token->token,
            ],
        ];
        return $res;
    }

    /**
     * [Generat URL]
     * @param  Request $request  [email, password]
     * @return [view]  [redirect to merchant confirm page or redirect back]
     */
    public function generatedUrl(Request $request)
    {
    	
		
        if (!auth()->check())
        {
            if ($_POST)
            {
                $credentials = $request->only('email', 'password');
                if (Auth::attempt($credentials))
                {
                    $this->setDefaultSessionValues();

                    $credentialsForConfirmPageLogin = [
                        'email'    => $request->email,
                        'password' => $request->password,
                    ];
                    Session::put('credentials', $credentialsForConfirmPageLogin);

                    //
                    $total_amount = 0;
        			$total_data = array();
                	$transInfos = array();
        
        			$grant_ids = $request->grant_ids;
        			$tokens = $request->tokens;
        
        			$grant_ids = json_decode($grant_ids);
        			$tokens = ltrim($tokens, '['); 
        			$tokens = substr($tokens, 0, -1); 
        			$tokens = explode(',', $tokens);
        
        			for($i = 0; $i < count($grant_ids); $i ++) {
                    
            			$transInfo = AppTransactionsInfo::with([
                			'app:id,merchant_id',
                			'app.merchant:id,user_id,business_name,fee',
                			'app.merchant.user:id,first_name,last_name',
            			])
                		->where(['grant_id' => $grant_ids[$i], 'token' => $tokens[$i], 'status' => 'pending'])->where('expires_in', '>=', time())
               			->first(['id', 'app_id', 'payment_method', 'currency', 'amount', 'success_url']);
            			//dd($tokens[$i]);
				
            			//Abort if logged in user is same as merchant
            			if ($transInfo->app->merchant->user->id == auth()->user()->id)
            			{
               				auth()->logout();
                			$this->helper->one_time_message('error', __('Merchant cannot make payment to himself!'));
                			return redirect()->back();
            			}
            			else
            			{
                			$data = $this->checkoutToPaymentConfirmPage($transInfo);
          					$total_amount += $transInfo->amount;
                			$total_data = $data;
                        	array_push($transInfos, $transInfo);
            			}	
        			}
                	//Put transaction informations to Session
        			Session::put('transInfo', $transInfos);
        			$total_data['total_amount']= $total_amount;
            		return view('merchantPayment.confirm', $total_data);
                }
                else
                {
                    $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                    return redirect()->back();
                }
            }
            else
            {
                $general         = Setting::where(['type' => 'general'])->get(['value', 'name'])->toArray();
                $data['setting'] = $setting = $this->helper->key_value('name', 'value', $general);
                return view('merchantPayment.login', $data);
            }
        }
        else
        {
        	$total_amount = 0;
        	$total_data = array();
        	$transInfos = array();
        
        	$grant_ids = $request->grant_ids;
        	$tokens = $request->tokens;
        
        	$grant_ids = json_decode($grant_ids);
        	$tokens = ltrim($tokens, '['); 
        	$tokens = substr($tokens, 0, -1); 
        	$tokens = explode(',', $tokens);
        	
        	for($i = 0; $i < count($grant_ids); $i ++) {
            	$transInfo = AppTransactionsInfo::with([
                	'app:id,merchant_id',
                	'app.merchant:id,user_id,business_name,fee',
                	'app.merchant.user:id,first_name,last_name',
            	])
                ->where(['grant_id' => $grant_ids[$i], 'token' => $tokens[$i], 'status' => 'pending'])->where('expires_in', '>=', time())
                ->first(['id', 'app_id', 'payment_method', 'currency', 'amount', 'success_url']);
            	//dd($tokens[$i]);
				// var_dump(auth()->user()->id);
            	//Abort if logged in user is same as merchant
            	if ($transInfo->app->merchant->user->id == auth()->user()->id)
            	{
               		auth()->logout();
                	$this->helper->one_time_message('error', __('Merchant cannot make payment to himself!'));
                	return redirect()->back();
            	}
            	else
            	{
                	$data = $this->checkoutToPaymentConfirmPage($transInfo);
          			$total_amount += $transInfo->amount;
                	// var_dump($transInfo->amount);
                	$total_data = $data;
                	array_push($transInfos, $transInfo);
            	}	
        	}
        	//Put transaction informations to Session
        	// var_dump("lll");
        	Session::put('transInfo', $transInfos);
        	$total_data['total_amount']= $total_amount;
            return view('merchantPayment.confirm', $total_data);
            //
        }
    }

    protected function checkoutToPaymentConfirmPage($transInfo)
    {
        // dd($transInfo->app->merchant->user->id);

        //check expired or not
        if (!$transInfo)
        {
            abort(403, 'Url has been deleted or expired.');
        }

        //check if currency exists in wallets
        $availableCurrency = [];
        $wallets           = Wallet::with(['currency:id,code'])->where(['user_id' => $transInfo->app->merchant->user->id])->get(['currency_id']); //2.3
        foreach ($wallets as $wallet)
        {
            $availableCurrency[] = $wallet->currency->code;
        }
        if (!in_array($transInfo->currency, $availableCurrency))
        {
            $this->helper->one_time_message('error', "You don't have the payment wallet. Please create wallet for currency - {$transInfo->currency} !");
            return redirect()->to('payment/fail');
        }

        $data['currSymbol'] = $currSymbol = Currency::where('code', $transInfo->currency)->first(['symbol'])->symbol;
        $data['transInfo']  = $transInfo;
        // dd($transInfo);
        //Put transaction informations to Session
        // Session::put('transInfo', $transInfo);
        return $data;
    }

    public function storeTransactionInfo(Request $request)
    {
 
        // dd($request->successUrl);
        $paymentMethod = $request->payer;
        $amount        = $request->amount;
        $currency      = $request->currency;
        $successUrl    = $request->successUrl;
        $cancelUrl     = $request->cancelUrl;
		
        # check token missing
    	$hasHeaderAuthorization = $request->header('Authorization');
    	
        if (!$hasHeaderAuthorization)
        {
            $res = [
                'status'  => 'error',
                'message' => 'Access token is missing',
                'data'    => [],
            ];
            return json_encode($res);
        }

        # check token authorization
        $headerAuthorization = $request->header('Authorization');
        $token               = $this->checkTokenAuthorization($headerAuthorization);

        # Currency Validation
        $res = $this->currencyValidaation($token, $currency);
        if (!empty($res['status']))
        {
            return json_encode($res);
        }

        # Amount Validation
        $res = $this->amountValidaation($amount);
        if (!empty($res['status']))
        {
            return json_encode($res);
        }

        if (false)
        {
            $res = [
                'status'  => 'error',
                'message' => 'Validation error',
                'data'    => [],
            ];
            return json_encode($res);
        }

        # Update/Create AppTransactionsInfo and return response
        $res = $this->updateOrAppTransactionsInfoAndReturnResponse($token->app_id, $paymentMethod, $amount, $currency, $successUrl, $cancelUrl);
        return json_encode($res);
			
    }

    /**
     * [Set Necessary Values To Session]
     */
    protected function setDefaultSessionValues()
    {
        $preferences = Preference::where('field', '!=', 'dflt_lang')->get();
        if (!empty($preferences))
        {
            foreach ($preferences as $pref)
            {
                $pref_arr[$pref->field] = $pref->value;
            }
        }
        if (!empty($preferences))
        {
            Session::put($pref_arr);
        }

        // default_currency
        $default_currency = Setting::where('name', 'default_currency')->first();
        if (!empty($default_currency))
        {
            Session::put('default_currency', $default_currency->value);
        }

        //default_timezone
        $default_timezone = auth()->user()->user_detail->timezone;
        if (!$default_timezone)
        {
            Session::put('dflt_timezone_user', session('dflt_timezone'));
        }
        else
        {
            Session::put('dflt_timezone_user', $default_timezone);
        }

        // default_language
        $default_language = Setting::where('name', 'default_language')->first();
        if (!empty($default_language))
        {
            Session::put('default_language', $default_language->value);
        }

        // company_name
        $company_name = Setting::where('name', 'name')->first();
        if (!empty($company_name))
        {
            Session::put('name', $company_name->value);
        }

        // company_logo
        $company_logo = Setting::where(['name' => 'logo', 'type' => 'general'])->first();
        if (!empty($company_logo))
        {
            Session::put('company_logo', $company_logo->value);
        }
    }

    /**
     * [check Token Authorization]
     * @param  [request] $headerAuthorization [header authorization request]
     * @return [string]  [token]
     */
    protected function checkTokenAuthorization($headerAuthorization)
    {
        $accessToken = $headerAuthorization;
        $tokenType   = '';
        $actualToken = '';
        if (preg_match('/\bBearer\b/', $accessToken))
        {
            $tokenType   = 'bearer';
            $t           = explode(' ', $accessToken);
            $key         = array_keys($t);
            $last        = end($key);
            $actualToken = $t[$last];
        }
        $token = AppToken::where('token', $actualToken)->where('expires_in', '>=', time())->first();
        if (!$token)
        {
            $res = [
                'status'  => 'error',
                'message' => 'Unauthorized token or token has been expired',
                'data'    => [],
            ];
            return json_encode($res);
        }
        return $token;
    }

    protected function currencyValidaation($token, $currency)
    {
        $acceptedCurrency = [];
        $wallets          = $token->app->merchant->user->wallets;
        foreach ($wallets as $wallet)
        {
            $acceptedCurrency[] = $wallet->currency->code;
        }
        //TODO:: Accepted currency will come from database or from merchant currency

        $res = ['status' => ''];
        if (!in_array($currency, $acceptedCurrency))
        {
            $res = [
                'status'  => 'error',
                'message' => 'Currency ' . $currency . ' is not supported by this merchant!',
                'data'    => [],
            ];
        }
        return $res;
    }

    protected function amountValidaation($amount)
    {
        $res = ['status' => ''];
        if ($amount <= 0)
        {
            $res = [
                'status'  => 'error',
                'message' => 'Amount cannot be 0 or less than 0.',
                'data'    => [],
            ];
        }
        return $res;
    }

    protected function updateOrAppTransactionsInfoAndReturnResponse($tokenAppId, $paymentMethod, $amount, $currency, $successUrl, $cancelUrl)
    {
        try
        {
            $grandId  = random_int(10000000, 99999999);
            $urlToken = str_random(20);

            AppTransactionsInfo::updateOrCreate([
                'app_id'         => $tokenAppId,
                'payment_method' => $paymentMethod,
                'amount'         => $amount,
                'currency'       => $currency,
                'success_url'    => $successUrl,
                'cancel_url'     => $cancelUrl,
                'grant_id'       => $grandId,
                'token'          => $urlToken,
                'expires_in'     => time() + (60 * 60 * 5), // url will expire in 5 hours after generation
            ]);

            $url = url("merchant/payment?grant_id=$grandId&token=$urlToken");
            $res = [
                'status'  => 'success',
                'message' => '',
                'data'    => [
                    'approvedUrl' => $url,
                ],
            ];
            return $res;
        }
        catch (\Exception $e)
        {
            print $e;
            exit;
        }
    }

    public function confirmPayment()
    {
    	// var_dump("llllllll");
        // dd(auth()->check());
        if (!auth()->check())
        {
            $getLoggedInCredentials = Session::get('credentials');
            if (Auth::attempt($getLoggedInCredentials))
            {
                $this->setDefaultSessionValues();
                $successPath = $this->storePaymentInformations();
                // dd($successPath);
                return redirect()->to($successPath);
            }
            else
            {
                $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                return redirect()->back();
            }
        }
    	
        $this->setDefaultSessionValues();
    	// var_dump($this->setDefaultSessionValues());die;
        $data = $this->storePaymentInformations();
    		
        
        if ($data['status'] == 200)
        {
            return redirect()->to($data['successPath']);
        }
        else
        {
            if ($data['status'] == 401)
            {
                $this->helper->one_time_message('error', 'Currency does not exist in the system!');
            }
            elseif ($data['status'] == 402)
            {
                $this->helper->one_time_message('error', "User doesn't have the wallet - {$data['currency']}. Please exchange to wallet - {$data['currency']}!");
            }
            elseif ($data['status'] == 403)
            {
                $this->helper->one_time_message('error', "User doesn't have sufficient balance!");
            }
            return redirect()->to('payment/fail');
        }
        Session::forget('transInfo');
    }


	protected function transactionBalance($charge, $amoun, $rest, $curr_id, $transInfo, $user_id, $uni_code) {
    
                	$data = [];
    
       				\DB::beginTransaction();
                    //Check User has the wallet or not
                    $senderWallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $curr_id])->first(['id', 'balance']);
                    $senderWallet->balance = $rest;
                    $senderWallet->save();

                    //Add on merchant
                    $merchantPayment                    = new MerchantPayment();
                    $merchantPayment->merchant_id       = $transInfo->app->merchant_id;
                    $merchantPayment->currency_id       = $curr_id;
                    $merchantPayment->payment_method_id = 1;
                    $merchantPayment->user_id           = $user_id;
                    $merchantPayment->gateway_reference = $uni_code;
                    $merchantPayment->order_no          = '';
                    $merchantPayment->item_name         = '';
                    $merchantPayment->uuid              = $uni_code;
                    $merchantPayment->charge_percentage = $charge;
                    $merchantPayment->charge_fixed      = 0;
                    $merchantPayment->amount            = $amoun - $charge;
                    $merchantPayment->total             = $amoun;
                    $merchantPayment->status            = 'Success';
                    //dd($merchantPayment);
                    $merchantPayment->save();

                    $transaction_A                           = new Transaction();
                    $transaction_A->user_id                  = $user_id;
                    $transaction_A->end_user_id              = $transInfo->app->merchant->user_id;
                    $transaction_A->merchant_id              = $transInfo->app->merchant_id;
                    $transaction_A->currency_id              = $curr_id;
                    $transaction_A->payment_method_id        = 1;
                    $transaction_A->uuid                     = $uni_code;
                    $transaction_A->transaction_reference_id = $merchantPayment->id;
                    $transaction_A->transaction_type_id      = Payment_Sent;
                    $transaction_A->subtotal                 = $amoun;
                    $transaction_A->percentage               = $transInfo->app->merchant->fee;
                    $transaction_A->charge_percentage        = 0;
                    $transaction_A->charge_fixed             = 0;
                    $transaction_A->total                    = '-' . ($transaction_A->subtotal);
                    $transaction_A->status                   = 'Success';
                    // dd($transaction_A);
                    $transaction_A->save();

                    $transaction_B                           = new Transaction();
                    $transaction_B->user_id                  = $transInfo->app->merchant->user_id;
                    $transaction_B->end_user_id              = $user_id;
                    $transaction_B->merchant_id              = $transInfo->app->merchant_id;
                    $transaction_B->currency_id              = $curr_id;
                    $transaction_B->payment_method_id        = 1;
                    $transaction_B->uuid                     = $uni_code;
                    $transaction_B->transaction_reference_id = $merchantPayment->id;
                    $transaction_B->transaction_type_id      = Payment_Received;
                    $transaction_B->subtotal                 = $amoun - ($charge);
                    $transaction_B->percentage               = $transInfo->app->merchant->fee; //fixed
                    $transaction_B->charge_percentage        = $charge;
                    $transaction_B->charge_fixed             = 0;
                    $transaction_B->total                    = $transaction_B->charge_percentage + $transaction_B->subtotal;
                    $transaction_B->status                   = 'Success';
                    // dd($transaction_B);
                    $transaction_B->save();


                    $transInfo->status = 'success';
                    $transInfo->save();

                    //updating/Creating merchant wallet
                    $merchantWallet          = Wallet::where(['user_id' => $transInfo->app->merchant->user_id, 'currency_id' =>$curr_id])->first(['id', 'balance']);
                    if (empty($merchantWallet))
                    {
                    $wallet              = new Wallet();
                    $wallet->user_id     = $transInfo->app->merchant->user_id;
                    $wallet->currency_id = $curr_id;
                    $wallet->balance     = ($amoun - ($charge));
                    $wallet->is_default  = 'No';
                    $wallet->save();
                    }
                    else
                    {
                    $merchantWallet->balance = $merchantWallet->balance + ($amoun - ($charge)); //fixed -- not amount with fee(total); only amount)
                    $merchantWallet->save();
                    }
                    \DB::commit();
                    $response = [
                    'status'         => 200,
                    'transaction_id' => $merchantPayment->uuid,
                    'merchant'       => $merchantPayment->merchant->user->first_name . ' ' . $merchantPayment->merchant->user->last_name,
                    'currency'       => $merchantPayment->currency->code,
                    'fee'            => $merchantPayment->charge_percentage,
                    'amount'         => $merchantPayment->amount,
                    'total'          => $merchantPayment->total,
                    ];
                    //dd($response);
                    $response            = json_encode($response);
                    $encodedResponse     = base64_encode($response);
                    $successPath         = $transInfo->success_url . '?' . $encodedResponse;
                    $data['status']      = 200;
                    $data['successPath'] = $successPath;
    
    				return $data;

 }
 protected function storePaymentInformations()
    {
        $transInfos = Session::get('transInfo');
    	
    	$totalAmount = 0;
    
    	foreach($transInfos as $transInfo) {
        	$totalAmount += $transInfo->amount;
        }
    	
    	$allMoneys = Wallet::join('currencies', function ($join)
        				{
            				$join->on('currencies.id', '=', 'wallets.currency_id');
        				})
        			->where(['user_id' => auth()->user()->id])
        			->orderBy('wallets.balance', 'ASC')
        			->select([
            			'wallets.balance as balance',
            			'wallets.id as id',
            			'currencies.rate as rate',
            			'currencies.code as code',
                    	'currencies.id as curr_id'
        			])
        			->get();
 		
        
    	$fflag = false;
 		$availableB = 0;
 		$requestB = 0;
    	foreach($allMoneys as $money) {
        	$availableB += ($money->balance * $money->rate);
        }
 
 		foreach($transInfos as $transInfo) {
        	$curr = Currency::where('code', $transInfo->currency)->first(['rate']);
        	$requestB += ($transInfo->amount * $curr->rate);
        }
 		
 		if($availableB > $requestB) {
        	$fflag = true;
        }
    	if(!$fflag) {
        
        	\DB::rollBack();
            $data['status'] = 403;
            return $data;
        
        } else {
        
    	$i = 0;
    	foreach($transInfos as $transInfo) {
        	
        	$i ++;
    	
        	$unique_code = unique_code();
        	$amount      = $transInfo->amount;
        	$currency    = $transInfo->currency;
        	$p_calc      = ($transInfo->app->merchant->fee / 100) * $amount;
		
        	//Check currency exists in system or not
        	$curr = Currency::where('code', $currency)->first(['id']);
        	// var_dump($curr->rate);die;
        	if (!$curr)
        	{
            	// dd('here');
            	\DB::rollBack();
            	$data['status'] = 401;
            	return $data;
        	}

        
        try
        {
				$diffBalance = $transInfo->amount;
				$currencyRate = Currency::where('code', $transInfo->currency)->first(['rate']);
				$thisCurrRate = $currencyRate->rate;
				
				$allMoney = Wallet::join('currencies', function ($join)
        				{
            				$join->on('currencies.id', '=', 'wallets.currency_id');
        				})
        			->where(['user_id' => auth()->user()->id])
        			->orderBy('wallets.balance', 'ASC')
        			->select([
            			'wallets.balance as balance',
            			'wallets.id as id',
            			'currencies.rate as rate',
            			'currencies.code as code',
                    	'currencies.id as curr_id'
        			])
        			->get();
				
        		$allMoneysArr = array();
        		foreach($allMoney as $a) {
            		if(floatval($a->balance) != '') {
                		array_push($allMoneysArr, $a);
            		}
        		}
        
              	$data = array();
            	foreach($allMoneysArr as $money) {
                	
        			$convertBalance = floatval($money->balance) * floatval($money->rate) / $thisCurrRate;
					if($convertBalance > floatval($diffBalance)) {
						
						$restBalance = $convertBalance - floatval($diffBalance);
                    	$restBalance = $restBalance * $thisCurrRate / $money->rate;
						$sendBalance = floatval($diffBalance) * $thisCurrRate / $money->rate;
                        $thisP_calc      = ($transInfo->app->merchant->fee / 100) * $sendBalance;
                    	$totalAmount = $totalAmount - $sendBalance * $money->rate / $thisCurrRate;
						$data = $this->transactionBalance( $thisP_calc, $sendBalance, $restBalance, $money->curr_id, $transInfo, auth()->user()->id, $unique_code);
                    
						break;
						
					} else {
						$restBalance = 0;
						$sendBalance = $convertBalance * $thisCurrRate / $money->rate;
						$diffBalance = floatval($diffBalance) - $convertBalance;
                        $thisP_calc      = ($transInfo->app->merchant->fee / 100) * $sendBalance;
						$totalAmount = $totalAmount - $sendBalance * $money->rate / $thisCurrRate;
						$data = $this->transactionBalance( $thisP_calc, $sendBalance, $restBalance, $money->curr_id, $transInfo, auth()->user()->id, $unique_code);
						
					}
                	
        		}
        		if($totalAmount == 0) {
                	return $data;
                }

        }
        catch (\Exception $e)
        {
        	\DB::rollBack();
        	$this->helper->one_time_message('error', $e->getMessage());
        	
        	return redirect()->to('payment/fail');
        
        }
       	
        
		
    }
        exit;
	}
	}
    public function cancelPayment()
    {
        $transInfo     = Session::get('transInfo');
        // dd($transInfo);
        $trans         = AppTransactionsInfo::find($transInfo->id, ['id', 'status', 'cancel_url']);
        $trans->status = 'cancel';
        $trans->save();
        Session::forget('transInfo');
        return redirect()->to($trans->cancel_url);
    }
}
