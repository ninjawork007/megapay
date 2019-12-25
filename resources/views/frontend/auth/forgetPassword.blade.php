@extends('frontend.layouts.app')
@section('content')
    <section class="inner-banner">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>@lang('message.form.forget-password-form')</h1>
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
                                    <h3 class="mb-0 text-left">@lang('message.form.forget-password-form')</h3>
                                </div>
                                <div class="card-body">
                                    <style>
                                        .error{
                                            font-weight: bold;
                                        }
                                    </style>
                                    @include('frontend.layouts.common.alert')
                                    <br>

                                    <form action="{{ url('forget-password') }}" method="post" id="forget-password-form">
                                            {{ csrf_field() }}
                                        <div class="form-group">
                                            <label for="email">@lang('message.form.email')</label>
                                            <input type="email" class="form-control" aria-describedby="emailHelp" placeholder="@lang('message.form.email')" name="email" id="email">
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                            <button type="submit" class="btn btn-cust float-right">@lang('message.form.submit')</button>
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
    <script>

        jQuery.extend(jQuery.validator.messages, {
            required: "{{__('This field is required.')}}",
            email: "{{__("Please enter a valid email address.")}}",
        });

        $('#forget-password-form').validate({
            rules: {
                email: {
                    required: true,
                    email: true,
                }
            }
        });
    </script>

@endsection