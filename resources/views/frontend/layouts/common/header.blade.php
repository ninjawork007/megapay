<?php
$user = Auth::user();
$socialList = getSocialLink();
$menusHeader = getMenuContent('Header');
//$logo = getCompanyLogo(); //from session
$logo = getCompanyLogoWithoutSession(); //direct query
?>
<header id="js-header-old">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0) 100%);background-color:transparent " >
        <div class="container">
            @if($logo)
                <a style="height: 45px;width: 157px;overflow: hidden;"  class="navbar-brand" href="@if (request()->path() != 'merchant/payment') {{ url('/') }} @else {{ '#' }} @endif">
                    <img src="{{asset('public/images/logos/'.$logo)}}" alt="logo" class="img-responsive img-fluid">
                </a>
            @else
                <a style="height: 45px;width: 157px;overflow: hidden;"  class="navbar-brand" href="@if (request()->path() != 'merchant/payment') {{ url('/') }} @else {{ '#' }} @endif">
                    <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" class="img-responsive" width="80" height="50">
                </a>
            @endif

            @if (request()->path() != 'merchant/payment')
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse navbar-toggler-right" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto my-navbar">
                        <li class="nav-item <?= isset( $menu ) && ( $menu == 'home' ) ? 'nav_active': '' ?>" style="margin-right:20px;"><a href="{{url('/')}}" class="nav-link">@lang('message.home.title-bar.home')</a></li>
                        <li class="nav-item <?= isset( $menu ) && ( $menu == 'send-money' ) ? 'nav_active': '' ?>" style="margin-right:20px;"><a href="{{url('/send-money')}}" class="nav-link">@lang('message.home.title-bar.send')</a></li>
                        <li class="nav-item <?= isset( $menu ) && ( $menu == 'request-money' ) ? 'nav_active': '' ?>" style="margin-right:20px;"><a href="{{url('/request-money')}}" class="nav-link">@lang('message.home.title-bar.request')</a></li>
                     @if(!empty($menusHeader))
                        @foreach($menusHeader as $top_navbar)
                            <li class="nav-item <?= isset( $menu ) && ( $menu == $top_navbar->url ) ? 'nav_active': '' ?>"><a href="{{url($top_navbar->url)}}" class="nav-link"> {{ $top_navbar->name }}</a></li>
                        @endforeach
                    @endif
                        @if( !Auth::check() )
                            <li class="nav-item auth-menu"> <a href="{{url('/login')}}" class="nav-link">@lang('message.home.title-bar.login')</a></li>
                            <li class="nav-item auth-menu"> <a href="{{url('/register')}}" class="nav-link">@lang('message.home.title-bar.register')</a></li>
                        @else
                            <li class="nav-item auth-menu"> <a href="{{url('/dashboard')}}" class="nav-link">@lang('message.home.title-bar.dashboard')</a> </li>
                            <li class="nav-item auth-menu"> <a href="{{url('/logout')}}" class="nav-link">@lang('message.home.title-bar.logout')</a> </li>
                        @endif
                    </ul>
                </div>
            @endif

            <div id="quick-contact" class="collapse navbar-collapse">
                <ul class="ml-auto">
                    @if( !Auth::check())
                        @if (request()->path() == 'merchant/payment')
                            {{-- @php
                                $grandId = $_GET['grant_id'];
                                $urlToken = $_GET['token'];
                            @endphp
                            <li> <a href="{{ url("merchant/payment?grant_id=$grandId&token=$urlToken") }}">@lang('message.home.title-bar.login')</a> </li> --}}
                        @else
                            <li> <a href="{{url('/login')}}">@lang('message.home.title-bar.login')</a> </li>
                            <li> <a href="{{url('/register')}}">@lang('message.home.title-bar.register')</a> </li>
                        @endif
                    @else
                        <li><a href="{{url('/dashboard')}}">@lang('message.home.title-bar.dashboard')</a> </li>
                        <li><a href="{{url('/logout')}}">@lang('message.home.title-bar.logout')</a> </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
</header>