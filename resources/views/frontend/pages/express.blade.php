@php
    $companyName = getCompanyName(); //DO NOT DELETE THIS - this is company name - coming from admin/settings
@endphp

@extends('frontend.layouts.app')
@section('content')
<!--Start Developer section-->
<section class="section-06 sector-guides">
    <div class="container">
        <div class="mt30">
            <div class="express-list">
                <ul>
                    <li>
                        <a href="{{url('/developer?type=standard')}}">
                            Standard
                        </a>
                    </li>
                    <li>
                        <a class="active33" href="{{url('/developer?type=express')}}">
                            Express
                        </a>
                    </li>
                    @if (!empty($publication_status) && $publication_status == 'Active')
                        <li><a href="{{url('/developer?type=woocommerce')}}">WooCommerce Plugin</a></li>
                    @endif
                </ul>
            </div>
            <p>
            </p>
            <div class="clearfix">
            </div>
        </div>
    </div>
</section>
<!--End Section -->

<!--Start Developer section-->
<section class="section-06 sector-guides mt50">
    <div class="container">
        <div class="h4 mt30 mb20 text-center">
            {{ $companyName }} Express Payment Gateway Documentation
        </div>
        <hr/>
        <div class="guidepara-style mt30">
            <div class="guide-header">
                <h3 class="guideTitle-color">
                    <strong>
                        Payer
                    </strong>
                </h3>
            </div>
            <p>
                If payer wants to fund payments using {{ $companyName }}, set payer to {{ $companyName }}.
                <br>
                (Other payment method ex: paypal, stripe, coinpayments etc not available yet).
            </br>
        </p>
    </div>
    <div class="guidepara-style mt30">
        <div class="guide-header">
            <h3 class="guideTitle-color">
                <strong>
                    Amount
                </strong>
            </h3>
        </div>
        <p>
            Specify a payment amount and the currency.
        </p>
    </div>
    <div class="guidepara-style mt30">
        <div class="guide-header">
            <h3 class="guideTitle-color">
                <strong>
                    Transaction
                </strong>
            </h3>
        </div>
        <p>
            It’s a Transaction resource where amount object has to set.
        </p>
    </div>
    <div class="guidepara-style mt30">
        <div class="guide-header">
            <h3 class="guideTitle-color">
                <strong>
                    RedirectUrls
                </strong>
            </h3>
        </div>
        <p>
            Set the urls where buyer should redirect after transaction is completed or
            cancelled.
        </p>
    </div>
    <div class="guidepara-style mt30">
        <div class="guide-header">
            <h3 class="guideTitle-color">
                <strong>
                    Payment
                </strong>
            </h3>
        </div>
        <p>
            It’s a payment resource where all
            <strong>
                Payer, Amount, RedirectUrls and
                Credentials of merchant
            </strong>
            (Client ID and Client Secret) have to set.
            After initialized into
            <strong>
                Payment
            </strong>
            object, need to call create
            method. It will generate a redirect URL. Users have to redirect into this URL to
            complete the transaction.
        </p>
    </div>
    <br>
    <div class="mt30">
        <h3>
            <strong>
                <ins>
                    Installation Instruction :
                </ins>
            </strong>
        </h3>
    </div>
    <div class="mt30">
        <div class="composer-box">
            Click download for the package
            <a href="{{url('download/package')}}" style="color:rgba(74, 111, 197, 0.9) !important">
                download
            </a>
        </div>
    </div>
    <div class="guidepara-style mt30">
        <div class="guideBox-color">
            {{-- <h3><strong>Note: <b style="text-decoration: underline;color: #c64e25;">setReturnUrl funcation has been changed to setSuccessUrl. So, if you are using Pay Money lower than v1.9, update your application's database with the update sql query, mentioned in 'section-sdk-update-query', from Pay Money v1.9 upgrade documentation.<br></b></strong>
            </h3>
            <br> --}}
            <h4>
                Now, go to
                <strong>
                    php-sdk/src/PayMoney/Rest/Connection.php,
                </strong>
                then change
                <strong>
                    BASE_URL
                </strong>
                value to your domain name
                <br>

                <span style="color:#707070;">
                (i.e:
                If the domain is - 'your-domain.com' then,
                <strong>
                    define( 'BASE_URL' , 'http://your-domain.com/' )
                </strong>
                )</span>

            </br>
            </h4>
        </div>
    </div>
    <div class="mt30">
        <div class="h4">
            Example :
        </div>
    </div>
<div class="guideBox-color mt30">
<pre style="display:block;">
    <span style="color:#cf1ffb;">require</span> <span style="color:#55bb17;">'vendor/autoload.php'</span>;

    <span style="color:#707070;">//if you want to change the namespace/path from 'PayMoney' - lines[1-5] - to your desired name,
    i.e. (use PayMoney\Api\Amount; to use MyDomain\Api\Amount;), then you must change the folders name that holds
    the API classes as well as change the property 'PayMoney' in (autoload->psr-0) of (php-sdk/composer.json) file to your
    desired name and run "composer dump-autoload" command from sdk root</span>

    <span style="color:#cf1ffb;">use</span> PayMoney\Api\Payer;
    <span style="color:#cf1ffb;">use</span> PayMoney\Api\Amount;
    <span style="color:#cf1ffb;">use </span>PayMoney\Api\Transaction;
    <span style="color:#cf1ffb;">use</span> PayMoney\Api\RedirectUrls;
    <span style="color:#cf1ffb;">use</span> PayMoney\Api\Payment;

    <span style="color:#707070;">//Payer Object</span>
    <span style="color:#fd8f00;">$payer</span> = <span style="color:#cf1ffb;">new</span> Payer();
    <span style="color:#fd8f00;">$payer</span>->setPaymentMethod(<span style="color:#55bb17;">'PayMoney'</span>); <span style="color:#707070;">//preferably, your system name, example - PayMoney</span>

    <span style="color:#707070;">//Amount Object</span>
    <span style="color:#fd8f00;">$amountIns</span> = <span style="color:#cf1ffb;">new</span> Amount();
    <span style="color:#fd8f00;">$amountIns</span>->setTotal(20)->setCurrency(<span style="color:#55bb17;">'USD'</span>); <span style="color:#707070;">//must give a valid currency code and must exist in merchant wallet list</span>

    <span style="color:#707070;">//Transaction Object</span>
    <span style="color:#fd8f00;">$trans</span> =<span style="color:#cf1ffb;"> new</span> Transaction();
    <span style="color:#fd8f00;">$trans</span>->setAmount(<span style="color:#fd8f00;">$amountIns</span>);

    <span style="color:#707070;">//RedirectUrls Object</span>
    <span style="color:#fd8f00;">$urls</span> = <span style="color:#cf1ffb;">new</span> RedirectUrls();
    <span style="color:#fd8f00;">$urls</span>->setSuccessUrl(<span style="color:#55bb17;">'http://your-merchant-domain.com/example-success.php'</span>) <span style="color:#707070;">//success url - the merchant domain page,
    to redirect after successful payment, see sample example-success.php file in sdk root,
    example - http://techvill.net/PayMoney_sdk/example-success.php</span>
    ->setCancelUrl(<span style="color:#55bb17;">'http://your-merchant-domain.com/'</span>); <span style="color:#707070;">//cancel url - the merchant domain page, to redirect after
    cancellation of payment, example -  http://techvill.net/PayMoney_sdk/</span>

    <span style="color:#707070;">//Payment Object</span>
    <span style="color:#fd8f00;">$payment</span> = <span style="color:#cf1ffb;">new</span> Payment();
    <span style="color:#fd8f00;">$payment</span>->setCredentials([ <span style="color:#707070;">//client id &amp; client secret, see merchants->setting(gear icon)</span>
    <span style="color:#55bb17;">'client_id'</span> => <span style="color:#55bb17;">'place your client id here'</span>, <span style="color:#707070;">//must provide correct client id of an express merchant</span>
    <span style="color:#55bb17;">'client_secret'</span> => <span style="color:#55bb17;">'place your client secret here'</span> <span style="color:#707070;">//must provide correct client secret of an express merchant</span>
    ])->setRedirectUrls(<span style="color:#fd8f00;">$urls</span>)
    ->setPayer(<span style="color:#fd8f00;">$payer</span>)
    ->setTransaction(<span style="color:#fd8f00;">$trans</span>);

    <span style="color:#cf1ffb;">try</span> {
    <span style="color:#fd8f00;"> $payment</span>->create(); <span style="color:#707070;">//create payment</span>
     header(<span style="color:#55bb17;">"Location: "</span>.<span style="color:#fd8f00;">$payment</span>->getApprovedUrl()); <span style="color:#707070;">//checkout url</span>
    }<span style="color:#cf1ffb;"> catch</span> (\Exception <span style="color:#fd8f00;">$ex</span>) {
    <span style="color:#cf1ffb;"> print</span> <span style="color:#fd8f00;">$ex</span>;
    <span style="color:#cf1ffb;"> exit;</span>
    }
</pre>

    <div class="guidepara-style mt30">
        <div class="guide-header">
            <h3 class="guideTitle-color">
                <strong>
                    Optional Instructions
                </strong>
            </h3>
        </div>
        <br>
        <div>
            <h4>
               If you don't see changes after configuring and extracting SDK, go to your SDK root and run the commands below:-
                <p style="color: #c64e25;"><b>composer clear-cache</b></p>
                <p style="color: #c64e25;"><b>composer install</b></p>
                <p style="color: #c64e25;"><b>composer dump-autoload</b></p>
            </h4>
        </div>
    </div>

    <br>
    <br>

</div>
</section>
<!--End Section-->


@endsection

@section('js')
<script>
</script>
@endsection
