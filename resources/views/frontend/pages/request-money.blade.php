@extends('frontend.layouts.app')
@section('content')
    <!--Start banner Section-->
    <section class="welcome-area request-bg">
        <div class="overlay-banner-request"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="welcome-text ">
                        <h1>@lang('message.request-money.banner.title',['br'=>'<br>'])</h1>
                        <h2>@lang('message.request-money.banner.sub-title')</h2>

                        @if(Auth::check() == false)
                            <a href="{{url('register')}}" class="iphone-btn">
                                @lang('message.request-money.banner.sign-up')
                            </a>
                            <p>@lang('message.request-money.banner.already-signed') <a href="{{url('login')}}">@lang('message.request-money.banner.login')</a> @lang('message.request-money.banner.request-money')</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End banner Section-->

    <!--Start Section A-->
    <section class="section-01 padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="sec-title">
                        <h2>@lang('message.request-money.section-a.title')</h2>
                        <p>@lang('message.request-money.section-a.sub-title')</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="right-bar">
                        <h2><span>1</span>@lang('message.request-money.section-a.sub-section-1.title') </h2>
                        <p>@lang('message.request-money.section-a.sub-section-1.sub-title') </p>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="right-bar">
                        <h2><span>2</span> @lang('message.request-money.section-a.sub-section-2.title')</h2>
                        <p> @lang('message.request-money.section-a.sub-section-2.sub-title')</p>

                    </div>
                </div>
                <div class="col-md-4">
                    <div class="right-bar">
                        <h2><span>3</span>@lang('message.request-money.section-a.sub-section-3.title')</h2>
                        <p>@lang('message.request-money.section-a.sub-section-3.sub-title')</p>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section A-->

    <!--Start Section B-->
    <section class="padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <img src="{{ url('public/frontend/banner/square_cash_phone.jpg')}}" alt="Phone Image"
                         class="img-responsive img-fluid"/>
                </div>
                <div class="col-md-7">
                    <div class="sec-title-laptop">
                        <h2>@lang('message.request-money.section-b.title')</h2>
                        <p>@lang('message.request-money.section-b.sub-title')</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section B-->

    <!--Start Section C -->
    <section class="laptop-app  padding-60">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="sec-title-laptop">
                        <h2>@lang('message.request-money.section-c.title')</h2>
                        <p>@lang('message.request-money.section-c.sub-title')</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section C-->

    <!--Start Section D-->
    <section class="sending-money padding-60">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <img src="{{ url('public/frontend/banner/send-money-01.png') }}" alt="Phone Image"
                         class="img-responsive img-fluid"/>
                </div>
                <div class="col-md-6">
                    <div class="sec-title" style="padding-top: 50px;">
                        <h2>@lang('message.request-money.section-d.title')</h2>
                        <p>@lang('message.request-money.section-d.sub-title')</p>
                        <!-- <a href="#"> Learn more about Paymoney fees</a> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section D-->

@endsection
@section('js')
    <script>

    </script>
@endsection
