<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'deposit/payumoney_success',
        'deposit/payumoney_fail', //fixed - pm1.9
        'deposit/payeer/payment/status',
        'deposit/checkout/payment/success', //fixed - pm1.9
        'merchant/api/*',
        'payment/form',
        'payment/payumoney_success',
        'payment/payumoney_fail', //fixed - pm_v2.3
        '/admin/dispute/change_reply_status',
        'ticket/change_reply_status',
        'request_payment/cancel',
        // 'deposit/ipn/perfect_money',
    ];
}
