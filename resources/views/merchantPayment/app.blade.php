<!DOCTYPE html>
<html lang="en">
    <head>
      <title>@lang('message.express-payment-form.merchant-payment')</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="{{ asset('public/backend/bootstrap/dist/css/bootstrap.css') }}">
      <script src="{{ asset('public/backend/jquery/dist/jquery.js') }}"></script>
      <script src="{{ asset('public/backend/bootstrap/dist/js/bootstrap.min.js') }}"></script>
      <!-- stripe -->
      <script src="{{ asset('public/dist/js/stripe-v3.js') }}"></script>
      <script type="text/javascript">
        var SITE_URL = "{{url('/')}}";
      </script>
      <style>
        /* --- pay-method --- */
        .plan-card-group {
          display: block;
          margin: auto;
          width: 100%;
          align-items: center;
          justify-content: space-around;
          flex-wrap: wrap;
        }
        .radio-card {
          width: 100%;
          margin-bottom: 30px;
        }
        .radio-card label {
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 0;
          height: 140px;
          background: #f9f9f9;
          border: 1px solid #e4e4e4;
          color: #003d2e;
          border-radius: 5px;
          transition: all 0.2s ease-in-out;
        }
        .radio-card label:hover {
          cursor: pointer;
          background: #ececec;
        }
        .radio-card label:active {
          background: #ececec;
          color: #ccf5eb;
        }
        .radio-card input[type="radio"]:checked ~ label {
          background: rgba(127, 103, 170, 0.2);
          color: white;
          border: 1px solid #cac8c8;
        }
        .card-title {
          display: block;
          font-size: 20px;
          width: 100%;
        }
        .planes-radio {
          display: none;
        }
        #plan-finalizar {
          display: block;
          margin: auto;
          padding: 15px 25px;
          border: none;
          border-radius: 5px;
          background: rgba(79, 179, 110, 0.35);
          color: white;
          font-size: 16px;
          transition: all 0.5s;
        }
        #plan-finalizar:hover {
          cursor: pointer;
          background: #00c291;
          color: white;
        }
        #plan-finalizar:focus, #plan-finalizar:active {
          outline: none;
          background: rgba(79, 179, 110, 0.35);
        }
        #plan-finalizar:disabled {
          background: #ddd;
          cursor: default;
        }
        fieldset {
          border: none;
        }
        legend {
          padding: 10px;
          font-size: 24px;
          font-weight: 300;
        }
        .padding-10 {
          padding: 10px;
        }
        .padding-20 {
          padding: 20px;
        }
        .padding-35 {
          padding: 35px;
        }
        .radio-card .fee { background: #7f67aa none repeat scroll 0 0;
          color: #fff;
          font-size: 12px;
          font-weight: bold;
          letter-spacing: 1px;
          padding: 0px 10px;
          position: absolute;
          right: 15px;
          top: 0;
          z-index: 4;
          line-height: 25px;
        }

        /*logo -- css*/
        .setting-img{
          overflow: hidden;
          max-width: 100%;
        }
        .img-wrap-general-logo {
          /*width: 300px;*/
          overflow: hidden;
          margin: 5px;
          background: rgba(74, 111, 197, 0.9) !important;
          /*height: 100px;*/
          max-width: 100%;
        }
        .img-wrap-general-logo > img {
          max-width: 100%;
          height: auto !important;
          max-height: 100%;
          width: auto !important;
          object-fit: contain;
        }
        /*logo -- css*/
      </style>
    </head>

    <body>

      <div class="container">
        <div class="row">
          <div class="col-md-4 col-sm-4"></div>
          <div class="col-md-4 col-sm-4"></div>
          <div class="col-md-2 col-sm-4">
            <h2>@lang('message.footer.language')</h2>
            <div class="form-group">
              <select class="form-control" id="lang">
                @foreach (getLanguagesListAtFooterFrontEnd() as $lang)
                <option {{ Session::get('dflt_lang') == $lang->short_name ? 'selected' : '' }}
                  value='{{ $lang->short_name }}'> {{ $lang->name }}
                </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="container text-center">
        <div class="row">
          <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default box-shadow" style="margin-top: 15px;">
              <div class="panel-body">
                <div class="row">
                  <div class="col-md-12">
                    @if($isMerchantAvailable)
                    <h1>
                     <?php
                     $amount = isset($paymentInfo['amount']) ? $paymentInfo['amount'] : 0;
                     ?>
                     {{ moneyFormat($merchant->currency->code, formatNumber($amount)) }}
                   </h1>
                   <h4>{{$paymentInfo['item_name'] ? $paymentInfo['item_name']:""}}</h4>
                   @endif
                 </div>
                  <div class="col-md-12">
                    <div class="bs-callout bs-callout-danger">
                      @if(!$isMerchantAvailable)
                        <h4 style="color:red">@lang('message.express-payment-form.merchant-not-found')</h4>
                      @else
                        <p>@lang('message.express-payment-form.merchant-found')</p>
                      @endif
                    </div>

                    @if($isMerchantAvailable)
                      <div class="row">
                        <div class="col-md-12">
                          <!-- Tab panes -->
                          <div class="tab-content">
                            <div class="tab-pane active" id="home">
                              <form id="check" action="" style="display: block;">
                                <div class="plan-card-group">
                                  <div class="row">

                                    @if(!empty($payment_methods))
                                      @foreach($payment_methods as $value)
                                        @php
                                        $name = strtolower($value['name']).'.jpg';
                                        @endphp

                                        @if(!in_array($value['id'],[4,6]) && in_array($value['id'],$cpm))
                                          <div class="col-md-4 col-xs-4">
                                            <div class="radio-card">
                                              <input class="planes-radio" name="method" value="{{$value['name']}}" id="{{$value['id']}}" type="radio">
                                              <label for="{{$value['id']}}" id="{{$value['id']}}">
                                                <span class="card-title">
                                                  @if($value['id']==1)
                                                    @if (!empty(getCompanyLogoWithoutSession()))
                                                    <div class="setting-img">
                                                      <div class="img-wrap-general-logo">
                                                        <img class="img-responsive" src='{{asset("public/images/logos/".getCompanyLogoWithoutSession())}}' alt="">
                                                      </div>
                                                    </div>
                                                    @else
                                                    <div class="setting-img">
                                                      <div class="img-wrap-general-logo">
                                                        <img class="img-responsive" src='{{asset("public/uploads/userPic/default-logo.jpg")}}' alt="">
                                                      </div>
                                                    </div>
                                                    @endif
                                                  @else
                                                    <img class="img-responsive" src='{{asset("public/images/payment_gateway/$name")}}' alt="">
                                                  @endif
                                                </span>
                                              </label>
                                            </div>
                                          </div>
                                        @endif
                                      @endforeach
                                    @endif

                                    <div class="col-md-12">
                                      <div class="pull-right">
                                        <a href="#payment" data-toggle="tab" class="btn btn-primary">
                                          @lang('message.express-payment-form.continue')
                                        </a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </form>
                            </div>

                            <div class="tab-pane" id="payment">
                              <!--- MTS GATEWAY START-->
                              <form action="{{url('payment/mts_pay')}}" id="Mts" name="Mts" method="POST" accept-charset="utf-8" style="display: none;">
                               {{csrf_field()}}
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="form-group">
                                      <label for="exampleInputEmail1">@lang('message.express-payment-form.email')</label>
                                      <input class="form-control" name="email" id="login" placeholder="Email" type="text" required>
                                    </div>
                                    <div class="form-group">
                                      <label for="exampleInputEmail1">@lang('message.express-payment-form.password')</label>
                                      <input name="password" class="form-control" id="password" placeholder="********" type="password" required>
                                    </div>
                                  </div>
                                  <div class="col-md-12">
                                    <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                    <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                    <input name="amount" value="{{ $amount }}" type="hidden">
                                    <input name="currency" value="{{$merchant->currency->code}}" type="hidden">
                                    <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                    <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- MTS GATEWAY END-->

                              <!--- PAYPAL GATEWAY START-->
                              <form id="Paypal" name="Paypal" method="post" action="{{url('payment/paypal')}}" accept-charset="UTF-8" style="display: none;">
                                {{csrf_field()}}
                                <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                <input name="no_shipping" value="1" type="hidden">
                                <input name="currency" value="{{$merchant->currency->code}}" type="hidden">
                                <input class="form-control" name="amount" value="{{ $amount }}" type="hidden">
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="bs-callout-warning">
                                      <p>@lang('message.express-payment-form.payment-agreement')</p>
                                    </div>
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- PAYPAL GATEWAY END-->

                              <!--- STRIPE GATEWAY START-->
                              <form id="Stripe" name="Stripe" method="post" action="{{url('payment/stripe')}}" accept-charset="UTF-8" style="display: none;">
                                {{ csrf_field() }}
                                <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                <input name="currency" value="{{$merchant->currency->code}}" type="hidden">
                                <input class="form-control" name="amount" value="{{ $amount }}" type="hidden">
                                <div class="form-row">
                                  <label for="card-element">
                                    @lang('message.express-payment-form.debit-credit-card')
                                  </label>
                                  <div id="card-element">
                                    <!-- a Stripe Element will be inserted here. -->
                                  </div>
                                  <!-- Used to display form errors -->
                                  <div id="card-errors" role="alert"></div>
                                </div>
                                <div class="row">
                                  <div class="col-md-12">
                                    <br>
                                    <br>
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- STRIPE GATEWAY END-->


                              <!--- Checkout2 GATEWAY START-->
                              <form id="2Checkout" name="2Checkout" method="post" action="{{url('payment/twocheckout')}}" accept-charset="UTF-8" style="display: none;">
                                {{csrf_field()}}
                                <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                <input name="currency" value="{{$merchant->currency->code}}" type="hidden">
                                <input class="form-control" name="amount" value="{{ $amount }}" type="hidden">

                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="bs-callout-warning">
                                      <p>@lang('message.express-payment-form.payment-agreement')</p>
                                    </div>
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- Checkout2 GATEWAY END-->

                              <!--- PayUmoney GATEWAY START-->
                              <form id="PayUmoney" name="PayUmoney" method="post" action="{{url('payment/payumoney')}}" accept-charset="UTF-8" style="display: none;">
                                {{csrf_field()}}
                                <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                <input class="form-control" name="amount" value="{{ $amount }}" type="hidden">
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="bs-callout-warning">
                                      <p>@lang('message.express-payment-form.payment-agreement')</p>
                                    </div>
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- PayUmoney GATEWAY END-->

                              <!--- CoinPayments GATEWAY START-->
                              <form id="Coinpayments" name="coinpayments" method="post" action="{{url('payment/coinpayments')}}" accept-charset="UTF-8" style="display: none;">
                                {{csrf_field()}}
                                <input name="order_no" value="{{isset($paymentInfo['order']) ? $paymentInfo['order'] : ''}}" type="hidden">
                                <input name="item_name" value="{{isset($paymentInfo['item_name']) ? $paymentInfo['item_name'] : ''}}" type="hidden">
                                <input name="merchant" value="{{isset($paymentInfo['merchant_id']) ? $paymentInfo['merchant_id'] : ''}}" type="hidden">
                                <input name="merchant_uuid" value="{{isset($paymentInfo['merchant']) ? $paymentInfo['merchant'] : ''}}" type="hidden">
                                <input name="currency" value="{{isset($merchant->currency) ? $merchant->currency->code : ''}}" type="hidden">
                                <input class="form-control" name="amount" value="{{ $amount }}" type="hidden">
                                <div class="row">
                                  <div class="col-md-12">
                                    <div class="bs-callout-warning">
                                      <p>@lang('message.express-payment-form.payment-agreement')</p>
                                    </div>
                                    <div class="pull-right">
                                      <a href="#home" data-toggle="tab" class="btn btn-default">@lang('message.express-payment-form.cancel')</a>
                                      <button type="submit" class="btn btn-primary">@lang('message.express-payment-form.go-to-payment')</button>
                                    </div>
                                  </div>
                                </div>
                              </form>
                              <!--- CoinPayments GATEWAY END-->
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
        var forms = document.querySelectorAll('form');
        if (forms.length != 0)
        {
          forms[0].addEventListener("click", function(e)
          {
            if (e.target && e.target.nodeName == "INPUT")
            {
              hideFormsButFirst();
              setFormVisible(e.target.value);
            }
          });

          function hideFormsButFirst()
          {
            for (var i = 0; i < forms.length; ++i)
            {
              forms[i].style.display = 'none';
            }
            forms[0].style.display = 'block';
          }

          function setFormVisible(id)
          {
            id = id || "Mts";
            var form = document.getElementById(id);
            form.style.display = 'block';
          }

          function init()
          {
            hideFormsButFirst();
            setFormVisible();
          }
          init();
          // Stripe Implementation
          var stripe = Stripe('{{ isset($publishable) ? $publishable : '' }}');
          var elements = stripe.elements();
          var style = {
            base:
            {
              color: '#32325d',
              lineHeight: '24px',
              fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
              fontSmoothing: 'antialiased',
              fontSize: '16px',
              '::placeholder':
              {
                color: '#aab7c4'
              }
            },
            invalid:
            {
              color: '#fa755a',
              iconColor: '#fa755a'
            }
          };
          // Create an instance of the card Element
          var card = elements.create('card',
          {
            style: style
          });
          // Add an instance of the card Element into the `card-element` <div>
          card.mount('#card-element');
          // Handle real-time validation errors from the card Element.
          card.addEventListener('change', function(event)
          {
            var displayError = document.getElementById('card-errors');
            if (event.error)
            {
              displayError.textContent = event.error.message;
            }
            else
            {
              displayError.textContent = '';
            }
          });
          // Handle form submission
          var form = document.getElementById('Stripe');
          form.addEventListener('submit', function(event)
          {
            event.preventDefault();
            stripe.createToken(card).then(function(result)
            {
              if (result.error)
              {
                      // Inform the user if there was an error
                      var errorElement = document.getElementById('card-errors');
                      errorElement.textContent = result.error.message;
                    }
                    else
                    {
                      // Send the token to your server
                      stripeTokenHandler(result.token);
                    }
                  });
          });

          function stripeTokenHandler(token)
          {
            // Insert the token ID into the form so it gets submitted to the server
            var form = document.getElementById('Stripe');
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'stripeToken');
            hiddenInput.setAttribute('value', token.id);
            form.appendChild(hiddenInput);
            // Submit the form
            form.submit();
          }
        }

        //Language script
        $('#lang').on('change', function(e)
        {
            e.preventDefault();
            lang = $(this).val();
            url = '{{ url('change-lang') }}';
            $.ajax(
            {
                type: 'get',
                url: url,
                data:
                {
                    lang: lang
                },
                success: function(msg)
                {
                    if (msg == 1)
                    {
                        location.reload();
                    }
                }
            });
        });
      </script>
    </body>
</html>