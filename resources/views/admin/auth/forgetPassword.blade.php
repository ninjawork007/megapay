<?php
/**
 * Created By: TechVillage.net
 * Start Date: 22-Jan-2018
 */
$logo = getCompanyLogoWithoutSession();
//dd($logo);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="parvez">
    <title>Admin</title>

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/bootstrap/dist/css/bootstrap.min.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{asset('public/backend/font-awesome-4.7.0/css/font-awesome.min.css')}}">

    <!-- Theme style -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/AdminLTE.min.css') }}">

</head>

<body class="hold-transition login-page" style="background-color:rgba(74, 111, 197, 0.9);">
<div class="login-box">
    <div class="login-logo">
        @if(!empty($logo))
            <a href="{{ url('admin/') }}"><img src='{{asset('public/images/logos/'.$logo)}}' class="img-responsive" width="282" height="63" style="width:75%; margin:auto;"></a>
        @else
            <img src='{{ url('public/uploads/userPic/default-logo.jpg') }}' class="img-responsive" width="282" height="63" style="width:75%; margin:auto;">
        @endif
    </div><!-- /.login-logo -->

    <div class="login-box-body" style="padding:40px 20px; box-shadow:0 0 5px #121212;">

        @if(Session::has('message'))
            <div class="alert {{ Session::get('alert-class') }} text-center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>{{ Session::get('message') }}</strong>
            </div>
        @endif

        <div class="alert alert-danger text-center" id="error_message_div" style="margin-bottom:0px;display:none;" role="alert">
            <p><a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a></p>
            <p id="error_message"></p>
        </div>
        <!-- /.Flash Message  -->

        <form action="{{ url('admin/forget-password') }}" method="post" id="forget-password-form">
            {{ csrf_field() }}

            <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
                <label class="control-label sr-only" for="inputSuccess2">Email</label>
                <input type="email" class="form-control" placeholder="Email" name="email">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                @if ($errors->has('email'))
                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>

            <div class="row">
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Submit</button>
                </div>
                <div class="col-xs-2">
                </div>
                <div class="col-xs-6">
                    <a href="{{url('admin')}}" class="btn btn-primary btn-block btn-flat">Back To Login</a><br>
                </div>
            </div>
        </form>
    <!-- /.social-auth-links -->
        {{-- <a href="{{url('/admin')}}">Already have an account</a><br>
        <a href="javascript:void(0)" class="text-center">Register a new membership</a> --}}
    </div>
    <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<!-- jQuery 3 -->
<script src="{{ asset('public/backend/jquery/dist/jquery.min.js') }}" type="text/javascript"></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ asset('public/backend/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script>
    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
    });

    $('#forget-password-form').validate({
        errorClass: "has-error",
        rules: {
            email: {
                required: true,
                email: true,
            },
        }
    });
</script>
</body>
