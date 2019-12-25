<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
 */

Route::group(['namespace' => 'Api'], function ()
{
    Route::get('get-preference-settings', 'LoginController@getPreferenceSettings');

    Route::get('check-login-via', 'LoginController@checkLoginVia');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout');

    //registration
    Route::get('check-merchant-user-role-existence', 'RegistrationController@getMerchantUserRoleExistence');
    Route::post('registration', 'RegistrationController@registration');
    Route::post('registration/duplicate-email-check', 'RegistrationController@duplicateEmailCheckApi');
    Route::post('registration/duplicate-phone-number-check', 'RegistrationController@duplicatePhoneNumberCheckApi');

    //Route for User Profile starts here
    Route::get('get-user-profile', 'ProfileController@getUserProfile');
    Route::post('update-user-profile', 'ProfileController@updateUserProfile');
    Route::get('get-user-specific-details', 'ProfileController@getUserSpecificProfile');
    Route::get('current-balance', 'ProfileController@getUserDefaultWalletBalance');
    Route::get('available-balance', 'ProfileController@getUserAvailableWalletsBalances');
    Route::post('profile/duplicate-email-check', 'ProfileController@userProfileDuplicateEmailCheckApi');
    //Route for User Profile Ends here

    //Route for Transactions starts here
    Route::get('activityall', 'TransactionController@getTransactionApi');
    Route::get('transaction-details', 'TransactionController@getTransactionDetailsApi');
    //Route for Transactions Ends here

    //Route for deposit Starts here
    Route::get('get-deposit-currency-list', 'DepositMoneyController@getDepositCurrencyList');
    Route::get('get-deposit-bank-list', 'DepositMoneyController@getDepositBankList');
    Route::post('fees-limit-currency-payment-methods-is-active-payment-methods-list', 'DepositMoneyController@getDepositMatchedFeesLimitsCurrencyPaymentMethodsSettingsPaymentMethods');
    Route::get('get-deposit-details-with-amount-limit-check', 'DepositMoneyController@getDepositDetailsWithAmountLimitCheck');
    Route::post('deposit/get-bank-detail', 'DepositMoneyController@getBankDetails');
    Route::post('deposit/bank-payment-store', 'DepositMoneyController@bankPaymentStore');
    Route::post('deposit/get-stripe-info', 'DepositMoneyController@getStripeInfo');
    Route::post('deposit/stripe-payment-store', 'DepositMoneyController@stripePaymentStore');
    Route::post('deposit/get-paypal-info', 'DepositMoneyController@getPaypalInfo');
    Route::post('deposit/paypal-payment-store', 'DepositMoneyController@paypalPaymentStore');
    //Route for deposit ends here

    //Route for withdraw Starts here
    Route::get('check-payout-settings', 'PayoutMoneyController@checkPayoutSettingsApi');
    Route::get('get-withdraw-payment-method', 'PayoutMoneyController@getWithdrawalPaymentMethod');
    Route::get('get-withdraw-currencies-based-on-payment-method', 'PayoutMoneyController@getWithdrawalCurrencyBasedOnPaymentMethod');
    Route::get('get-withdraw-details-with-amount-limit-check', 'PayoutMoneyController@getWithdrawDetailsWithAmountLimitCheck');
    Route::post('amount-limit-check-withdraw-money', 'PayoutMoneyController@amountLimitCheckWithdraw');
    Route::post('withdraw-money-pay', 'PayoutMoneyController@withdrawMoneyConfirm');
    //Route for withdraw ends here

    //Route for Send Money Starts here
    Route::get('check-processed-by', 'SendMoneyController@checkProcessedByApi');
    Route::post('send-money-email-check', 'SendMoneyController@postSendMoneyEmailCheckApi');
    Route::post('send-money-phone-check', 'SendMoneyController@postSendMoneyPhoneCheckApi');
    Route::get('get-send-money-currencies', 'SendMoneyController@getSendMoneyCurrenciesApi');
    Route::post('check-send-money-amount-limit', 'SendMoneyController@postSendMoneyFeesAmountLimitCheckApi');
    Route::post('send-money-pay', 'SendMoneyController@postSendMoneyPayApi');
    //Route for Send Money Ends here

    //Route for Request Money Starts here
    Route::post('request-money-email-check', 'RequestMoneyController@postRequestMoneyEmailCheckApi');
    Route::post('request-money-phone-check', 'RequestMoneyController@postRequestMoneyPhoneCheckApi');
    Route::get('get-request-currency', 'RequestMoneyController@getRequestMoneyCurrenciesApi');
    Route::post('request-money-pay', 'RequestMoneyController@postRequestMoneyPayApi');
    //Route for Request Money Ends here

    //Route for accept/cancel request payment starts here
    Route::get('accept-request-email-phone', 'AcceptCancelRequestMoneyController@getAcceptRequestEmailOrPhone');
    Route::post('request-accept-amount-limit-check', 'AcceptCancelRequestMoneyController@getAcceptRequestAmountLimit');
    Route::get('get-accept-fees-details', 'AcceptCancelRequestMoneyController@getAcceptFeesDetails');
    Route::post('accept-request-payment-pay', 'AcceptCancelRequestMoneyController@requestAcceptedConfirm');
    Route::post('cancel-request', 'AcceptCancelRequestMoneyController@requestCancel');
    //Route for accept/cancel request payment ends here

    //Route for exchange money starts here
    Route::get('get-User-Wallets-WithActive-HasTransaction', 'ExchangeMoneyController@getUserWalletsWithActiveAndHasTransactionCurrency');
    Route::post('exchange-review', 'ExchangeMoneyController@exchangeReview');
    Route::post('exchange-amount-limit-check', 'ExchangeMoneyController@exchangeAmountLimitCheck');
    Route::post('getBalanceOfFromAndToWallet', 'ExchangeMoneyController@getBalanceOfFromAndToWallet');
    Route::post('getWalletsExceptSelectedFromWallet', 'ExchangeMoneyController@getWalletsExceptSelectedFromWallet');
    Route::post('get-currencies-exchange-rate', 'ExchangeMoneyController@getCurrenciesExchangeRate');
    Route::post('review-exchange-details', 'ExchangeMoneyController@reviewExchangeDetails');
    Route::post('exchange-money-complete', 'ExchangeMoneyController@exchangeMoneyComplete');
    //Route for exchange money ends here
});

// Route::get('get_transaction', 'Api\TransactionController@getTransaction')->middleware(['auth:api', 'permission:manage_merchant']);//permission api test
