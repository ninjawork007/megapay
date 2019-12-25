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

        <!--css styles-->
        @include('user_dashboard.layouts.common.style')

        <!---title logo icon-->
        <link rel="javascript" href="{{asset('public/user_dashboard/js/respond.js')}}">

        <!---favicon-->
        @if (!empty(getfavicon()))
            <link rel="shortcut icon" href="{{asset('public/images/logos/'.getfavicon())}}" />
        @endif

        <script type="text/javascript">
            var SITE_URL = "{{url('/')}}";
        </script>

        <style type="text/css">
            #image-dropdown {
              display: inline-block;
              border: 1px solid;
            }
            #image-dropdown {
              height: 30px;
              overflow: hidden;
            }
            /*#image-dropdown:hover {} */

            #image-dropdown .img_holder {
              cursor: pointer;
            }
            #image-dropdown img.flagimgs {
              height: 30px;
            }
            #image-dropdown span.iTEXT {
              position: relative;
              top: -8px;
            }
            .navbar.navbar-expand-lg.navbar-dark.bg-primary.toogleMenuDiv{
                padding:0 !important;
            }
        </style>
    </head>
    <body>
        <!-- Start Preloader -->
        {{-- <div class="preloader">
            <div class="preloader-img"></div>
        </div> --}}
        <!-- End Preloader -->
        <!-- Start scroll-top button -->

        <div id="scroll-top-area">
            <a href="{{url()->current()}}#top-header"><i class="ti-angle-double-up" aria-hidden="true"></i></a>
        </div>

         @include('user_dashboard.layouts.common.header')

        @yield('content')

        @include('frontend.layouts.common.footer_menu')
        @include('user_dashboard.layouts.common.footer')
        @include('user_dashboard.layouts.common.script')
        @yield('js')
    </body>
</html>


