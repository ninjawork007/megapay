@extends('frontend.layouts.app')
@section('content')
    <section class="inner-banner">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>@lang('message.login.title') </h1>
                </div>
            </div>
        </div>
    </section>
    <!--End banner Section-->
    <!--Start Section-->
    <section class="section-01 padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <!-- form card login -->
                            <div class="card rounded-0">
                                <div class="card-header">
                                    <h3 class="mb-0 text-left">@lang('message.login.form-title')</h3>
                                </div>
                                <div class="card-body">

                                    @include('frontend.layouts.common.alert')
                                    <br>

                                    <form action="{{ request()->fullUrl() }}" method="post" id="login_form">
                                        {{ csrf_field() }}
                                        <div class="form-group">
                                            <label for="email">@lang('message.login.email')</label>
                                            <input type="email" class="form-control" aria-describedby="emailHelp" placeholder="@lang('message.login.email')" name="email" id="email">
                                        </div>

                                        <div class="form-group">
                                            <label for="password">@lang('message.login.password')</label>
                                            <input type="password" class="form-control" id="password" placeholder="@lang('message.login.password')" name="password">
                                        </div>

                                        @if (isset($setting['has_captcha']) && $setting['has_captcha'] == 'Enabled')
                                            <div class="row">
                                                <div class="col-md-12">
                                                    {!! app('captcha')->display() !!}
                                                    <br>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="row">
                                            <input class="form-check-input" type="hidden" value="" id="remember_me" name="remember_me">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-cust float-left">@lang('message.form.button.login')</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!--/card-block-->
                            </div>
                            <!-- /form card login -->
                        </div>
                    </div>
                    <!--/row-->
                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>
@endsection

@section('js')
    <script src="{{asset('public/frontend/js/jquery.validate.min.js')}}" type="text/javascript"></script>
    <script type="text/javascript">
        jQuery.extend(jQuery.validator.messages, {
            required: "{{__('This field is required.')}}",
            email: "{{__("Please enter a valid email address.")}}",
        })
    </script>
    <script>
        $('#login_form').validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                },
                password: {
                    required: true
                }
            }
        });
    </script>
@endsection