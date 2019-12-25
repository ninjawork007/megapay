@extends('frontend.layouts.app')
@section('content')

    <!--Start banner Section-->
    <section class="welcome-area send-money-bg">
        <div class="overlay-banner-send"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="welcome-text ">
                        <h1>@lang('message.send-money.banner.title')</h1>
                        <h2>@lang('message.send-money.banner.sub-title')</h2>
                        @if(Auth::check() == false)
                            <a href="{{url('register')}}" class="start-btn">
                                @lang('message.send-money.banner.sign-up')
                            </a>
                            <a href="{{url('login')}}" class="iphone-btn" style="margin-left: 20px;">
                                @lang('message.send-money.banner.login')
                            </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End banner Section-->

    <!--Start Section A -->
    <section class="section-01 padding-60">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="sec-title">
                        <h2>@lang('message.send-money.section-a.title')</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">

                    <div class="right-bar">
                        <h2><span>1</span> @lang('message.send-money.section-a.sub-section-1.title')</h2>
                        <p>@lang('message.send-money.section-a.sub-section-1.sub-title')</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="right-bar">
                        <h2><span>2</span>@lang('message.send-money.section-a.sub-section-2.title')</h2>
                        <p>@lang('message.send-money.section-a.sub-section-2.sub-title')</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="right-bar">
                        <h2><span>3</span>@lang('message.send-money.section-a.sub-section-3.title')</h2>
                        <p>@lang('message.send-money.section-a.sub-section-3.sub-title')</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section A-->

    <!--Start Section B -->
    <section class="mobile-app  padding-60">
        <div class="container">
            <div class="row">
                <div class="col-md-7"></div>
                <div class="col-md-5">
                    <div class="sec-title">
                        <h2>@lang('message.send-money.section-b.title')</h2>
                        <p>@lang('message.send-money.section-b.sub-title')</p>
                        <div style="display: inline-flex;">
                        @foreach(getAppStoreLinkFrontEnd() as $app)
                            @if (isset($app->logo))
                                <div class="store-logo">
                                <a href="{{$app->link}}"><img src="{{url('public/uploads/app-store-logos/'.$app->logo)}}" class="img-responsive" style="object-fit:contain;width: auto;height: 100%;max-width: 100%" /></a>
                                </div>
                            @else
                                <a href="#" style="width: 110px;height: 50px"><img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' class="img-responsive" style="width: auto;height: 100%;max-width: 100%"/></a>
                            @endif
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--End Section B-->

    <!--Start Section C -->
    <section class="sending-money padding-60" style="padding-bottom: 30px;">
        <div class="container">
            <div class="row">

                <div class="col-md-7">
                    <div class="sec-title" style="padding: 30px 0px;">
                        <h2>@lang('message.send-money.section-c.title')</h2>
                        <p>@lang('message.send-money.section-c.sub-title')</p>
                        <!-- <a href="#"> Learn more about Paymoney fees</a> -->
                    </div>
                </div>
                <div class="col-md-5">
                    <img src="{{url('public/frontend/banner/send-money.jpg')}}" alt="Phone Image"
                         class="img-responsive img-fluid"/>
                </div>
            </div>
        </div>
    </section>
    <!--End Section C-->



@endsection
@section('js')
    <script>

    </script>
@endsection
