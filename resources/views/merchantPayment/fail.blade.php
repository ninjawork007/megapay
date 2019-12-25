<!DOCTYPE html>
<html lang="en">
  <head>
    <title>@lang('message.express-payment-form.merchant-payment')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('public/backend/bootstrap/dist/css/bootstrap.css') }}">
    <script src="{{ asset('public/backend/jquery/dist/jquery.js') }}"></script>
    <script src="{{ asset('public/backend/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript">
      var SITE_URL = "{{URL::to('/')}}";
    </script>
    <style>
    </style>
  </head>

  <body>
    <div class="container text-center">

      @include('user_dashboard.layouts.common.alert')

      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="panel panel-default box-shadow" style="margin-top: 15px;">
            <div class="panel-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="alert alert-warning">
                    <strong>@lang('message.express-payment-form.sorry')</strong> @lang('message.express-payment-form.payment-unsuccessful')
                  </div>
                  <a href="{{url('/login')}}" class="btn btn-sm btn-info">@lang('message.express-payment-form.back-home')</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
    </script>
  </body>
</html>