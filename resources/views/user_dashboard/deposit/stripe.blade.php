@extends('user_dashboard.layouts.app')
@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mb20 marginTopPlus">
                    @include('user_dashboard.layouts.common.alert')
                    <div class="card">
                        <div class="card-header"><h4 class="float-left">@lang('message.dashboard.deposit.deposit-stripe-form.title')</h4></div>
                        <div class="card-body">
                            <form action="{{URL::to('deposit/stripe_payment_store')}}" method="post" id="payment-form">
                                <div class="row">
                                    {{ csrf_field() }}
                                    <div class="form-group col-md-6">
                                        <label for="usr">@lang('message.dashboard.deposit.deposit-stripe-form.card-no')</label>
                                        <div id="card-number"></div>
                                        <div id="card-errors" class="error"></div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="usr">{{ __('mm-yy') }}</label>
                                        <div id="card-expiry"></div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="usr">{{ __('cvc') }}</label>
                                        <div id="card-cvc"></div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <button class="btn btn-cust float-left" style="margin-top:10px;" type="submit">
                                            @lang('message.form.submit')
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('user_dashboard.layouts.common.help')
@endsection
@section('js')

    <script src="{{asset('public/dist/js/stripe-v3.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        // Create a Stripe client
        var stripe = Stripe('{{$publishable}}');
        // Create an instance of Elements
        var elements = stripe.elements();
        // Custom styling can be passed to options when creating an Element.
        // (Note that this demo uses a wider set of styles than the guide below.)
        var style = {
            base: {
                position: 'relative',
                display: 'block',
                width: '100%',
                height: '34px ',
                border: '1px solid #d2d2d2',
                padding: '0 12px',
                color: 'rgba(56, 56, 56, 0.85)',
                margin: '0 0 10px 0',
                background: '#FFFFFF',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };
        // Create an instance of the card Element
        var cardNumber = elements.create('cardNumber', {style: style});
        cardNumber.mount('#card-number');
        var cardExpiry = elements.create('cardExpiry', {style: style});
        cardExpiry.mount('#card-expiry');
        var cardCvc = elements.create('cardCvc', {style: style});
        cardCvc.mount('#card-cvc');
        // Handle real-time validation errors from the card Element.
        cardNumber.addEventListener('change', function (event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Handle form submission
        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            stripe.createToken(cardNumber).then(function (result) {
                if (result.error) {
                    // Inform the user if there was an error
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                    return false;
                } else {
                    // Send the token to your server
                    stripeTokenHandler(result.token);
                    form.submit();
                }
            });
        });

        function stripeTokenHandler(token) {
            $('#payment-form').append('<input type="hidden" name="stripeToken" value="' + token.id + '">');
        }

        $(document).ready(function() {
            window.history.pushState(null, "", window.location.href);
            window.onpopstate = function() {
                window.history.pushState(null, "", window.location.href);
            };
        });
    </script>
@endsection