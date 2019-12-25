<?php
$user = Auth::user();
$socialList = getSocialLink();
$menusHeader = getMenuContent('Header');
//$logo = session('company_logo'); //from session
$logo = getCompanyLogoWithoutSession(); //direct query
?>
<header id="js-header-old">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary toogleMenuDiv" style="max-height: 63px;">
        <div class="container">
            @if (isset($logo))
                <a style="height: 45px;width: 157px;overflow: hidden;"  class="navbar-brand" href="{{url('/')}}">
                    <img src="{{asset('public/images/logos/'.$logo)}}" alt="logo" class="img-responsive img-fluid">
                </a>
            @else
                <a style="height: 45px;width: 157px;overflow: hidden;"  class="navbar-brand" href="{{url('/')}}">
                    <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" class="img-responsive" width="80" height="50">
                </a>
            @endif

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse navbar-toggler-right" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto d-lg-none">
                    <li class="nav-item"><a href="{{url('/dashboard')}}" class="nav-link">@lang('message.dashboard.nav-menu.dashboard')</a></li>

                    @if(Common::has_permission(auth()->id(),'manage_transaction'))
                        <li class="nav-item"><a href="{{url('/transactions')}}" class="nav-link">@lang('message.dashboard.nav-menu.transactions')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_deposit'))
                        <li class="nav-item"><a href="{{url('/deposit')}}" class="nav-link">@lang('message.dashboard.button.deposit')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_transfer'))
                        <li class="nav-item"><a href="{{url('/moneytransfer')}}" class="nav-link">@lang('message.dashboard.nav-menu.send-req')</a></li>
                    @elseif(Common::has_permission(auth()->id(),'manage_request_payment'))
                        <li class="nav-item"><a href="{{url('/request_payment/add')}}" class="nav-link">@lang('message.dashboard.nav-menu.send-req')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_exchange'))
                        <li class="nav-item"><a href="{{url('/exchange')}}" class="nav-link">@lang('message.dashboard.nav-menu.exchange')</a></li>
                    @endif

                    <!--@if(Common::has_permission(auth()->id(),'manage_voucher'))
                        <li class="nav-item"><a href="{{url('/vouchers')}}" class="nav-link">@lang('message.dashboard.nav-menu.vouchers')</a></li>
                    @endif
                    -->
                    @if(Common::has_permission(auth()->id(),'manage_merchant'))
                        <li class="nav-item"><a href="{{url('/merchants')}}" class="nav-link">@lang('message.dashboard.nav-menu.merchants')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_withdrawal'))
                        <li class="nav-item"><a href="{{url('/payouts')}}" class="nav-link">@lang('message.dashboard.nav-menu.payout')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_dispute'))
                        <li class="nav-item"><a href="{{url('/disputes')}}" class="nav-link">@lang('message.dashboard.nav-menu.disputes')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_ticket'))
                        <li class="nav-item"><a href="{{url('/tickets')}}" class="nav-link">@lang('message.dashboard.nav-menu.tickets')</a></li>
                    @endif

                    @if(Common::has_permission(auth()->id(),'manage_setting'))
                        <li class="nav-item"><a href="{{url('/profile')}}" class="nav-link">@lang('message.dashboard.nav-menu.settings')</a></li>
                    @endif

                    <li class="nav-item"><a href="{{url('/logout')}}" class="nav-link">@lang('message.dashboard.nav-menu.logout')</a></li>
                </ul>
            </div>

            <div class="d-none d-lg-block" style="width: 229px;">
                <div class="row">
                    <div class="col-md-3" style="padding-top: 10px">
                        @if(Auth::user()->picture)
                            <img src="{{url('public/user_dashboard/profile/'.Auth::user()->picture)}}"
                                 class="rounded-circle rounded-circle-custom" id="profileImageHeader">
                        @else
                            <img src="{{url('public/user_dashboard/images/avatar.jpg')}}" class="rounded-circle rounded-circle-custom" id="profileImageHeader">
                        @endif
                    </div>

                    @php
                        $fullName = strlen($user->first_name.' '.$user->last_name) > 20 ? substr($user->first_name.' '.$user->last_name,0,20)."..." : $user->first_name.' '.$user->last_name; //change in pm_v2.1
                    @endphp
                    <div class="col-md-9 username text-left">

                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span id="subStringUserName" title="{{$user->first_name.' '.$user->last_name}}">{{$fullName}}</span></a> <!--change in pm_v2.1-->

                        <ul class="dropdown-menu" style="color:#545b62;min-width: 135px;">
                            @if(Common::has_permission(auth()->id(),'manage_setting'))
                                <li class="" style="padding: 5px;text-align: center;border-bottom: 1px solid #dae1e9">
                                    <i class="fa fa-cog"></i><a style="line-height: 0;color:#7d95b6" href="{{url('/profile')}}" class="btn btn-default btn-flat">@lang('message.dashboard.nav-menu.settings')</a>
                                </li>
                            @endif
                            <li class="" style="padding: 5px;text-align: center">
                                <i class="fa fa-sign-out"></i><a style="line-height: 0;color:#7d95b6" href="{{url('/logout')}}" class="btn btn-default btn-flat">@lang('message.dashboard.nav-menu.logout')</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!--Start Section-->
<section class="section-06 menu-bgcolor marginTopMinnus d-none d-lg-block">
    <div class="container">
        <div class="menu-list">
            <ul>
                <li class="<?= isset($menu) && ($menu == 'dashboard') ? 'active' : '' ?>"><a href="{{url('/dashboard')}}"><i class="fa fa-dashboard"></i>@lang('message.dashboard.nav-menu.dashboard')</a></li>

                @if(Common::has_permission(auth()->id(),'manage_transaction'))
                    <li class="<?= isset($menu) && ($menu == 'transactions') ? 'active' : '' ?>"><a href="{{url('/transactions')}}"><i class="fa fa-list"></i>@lang('message.dashboard.nav-menu.transactions')</a></li>
                @endif

                @if(Common::has_permission(auth()->id(),'manage_transfer'))
                    <li class="<?= isset($menu) && ($menu == 'send_receive') ? 'active' : '' ?>"><a href="{{url('/moneytransfer')}}"><i class="fa fa-exchange"></i>@lang('message.dashboard.nav-menu.send-req')</a></li>
                @elseif(Common::has_permission(auth()->id(),'manage_request_payment'))
                    <li class="<?= isset($menu) && ($menu == 'request_payment') ? 'active' : '' ?>">
                        <a href="{{url('/request_payment/add')}}"><i class="fa fa-exchange"></i>@lang('message.dashboard.nav-menu.send-req')</a>
                    </li>
                @elseif(Common::has_permission(auth()->id(),'manage_bank_transfer'))
                    <li class="<?= isset($menu) && ($menu == 'bank_transfer') ? 'active' : '' ?>">
                        <a href="{{url('/bank_transfer')}}"><i class="fa fa-exchange"></i>@lang('message.dashboard.nav-menu.send-to-bank')</a>
                    </li>
                @endif

            <!--@if(Common::has_permission(auth()->id(),'manage_voucher'))
                    <li class="<?= isset($menu) && ($menu == 'voucher') ? 'active' : '' ?>"><a href="{{url('/vouchers')}}"><i class="fa fa-gift"></i>@lang('message.dashboard.nav-menu.vouchers')</a></li>
                @endif
            -->
                @if(Common::has_permission(auth()->id(),'manage_merchant'))
                    <li class="<?= isset($menu) && ($menu == 'merchant') ? 'active' : '' ?>"><a
                                href="{{url('/merchants')}}"><i
                                    class="fa fa-user"></i>@lang('message.dashboard.nav-menu.merchants')</a></li>
                @endif
                @if(Common::has_permission(auth()->id(),'manage_dispute'))
                    <li class="<?= isset($menu) && ($menu == 'dispute') ? 'active' : '' ?>"><a
                                href="{{url('/disputes')}}"><i class="fa fa-ticket"></i>@lang('message.dashboard.nav-menu.disputes')</a></li>
                @endif
                @if(Common::has_permission(auth()->id(),'manage_ticket'))
                    <li class="<?= isset($menu) && ($menu == 'ticket') ? 'active' : '' ?>"><a
                                href="{{url('/tickets')}}"><i class="fa fa-spinner"></i>@lang('message.dashboard.nav-menu.tickets')</a></li>
                @endif

                {{-- <li class="active"><a href="https://your-site/your-external-link"><i class="fa fa-link"></i>External Link</a></li> --}}
            </ul>
        </div>
    </div>
</section>