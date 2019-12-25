<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{!isset($exception) ? meta(Route::current()->uri(),'description'):$exception->description}}">
    <meta name="keywords" content="{{!isset($exception) ? meta(Route::current()->uri(),'keyword'):$exception->keyword}}">
    <title>{{!isset($exception) ? meta(Route::current()->uri(),'title'):$exception->title}} <?= isset($additionalTitle)?'| '.$additionalTitle :'' ?></title>

    @include('frontend.layouts.common.style')

    <!---title logo icon-->
    <link rel="javascript" href="{{asset('public/frontend/js/respond.js')}}">

    <!---favicon-->
    @if (!empty(getfavicon()))
        <link rel="shortcut icon" href="{{asset('public/images/logos/'.getfavicon())}}" />
    @endif

    <script type="text/javascript">
        var SITE_URL = "{{url('/')}}";
    </script>
</head>


<body class="send-money request-page">

    <!-- Start Preloader -->
{{--     <div class="preloader">
        <div class="preloader-img"></div>
    </div> --}}
    <!-- End Preloader -->

    <!-- Start scroll-top button -->
    <div id="scroll-top-area">
        <a href="{{url()->current()}}#top-header"><i class="ti-angle-double-up" aria-hidden="true"></i></a>
    </div>
    <!-- End scroll-top button -->
    <!--Start Header-->
    @include('frontend.layouts.common.header')
    <!--End Header-->

    @yield('content')

    <!--Start Contact Section-->
    @include('frontend.layouts.common.footer_menu')
    <!--End Contact Section-->
    <!--Start Footer-->
    @include('frontend.layouts.common.footer')
    <!--End Footer-->
    @include('frontend.layouts.common.script')

    @yield('js')
</body>