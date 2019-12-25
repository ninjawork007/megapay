<ul class="sidebar-menu">
    <li <?= $menu == 'dashboard' ? ' class="active"' : 'treeview'?>>
        <a href="{{ url('admin/home') }}">
            <i class="fa fa-dashboard"></i><span>Dashboard</span>
        </a>
    </li>

    <!--users-->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_user') || Common::has_permission(\Auth::guard('admin')->user()->id, 'view_admins'))

        <li <?= $menu == 'users' ? ' class="active treeview"' : 'treeview'?> >
            <a href="#">
                <i class="glyphicon glyphicon-user"></i><span>Users</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_user'))
                    <li <?= isset($sub_menu) && $sub_menu == 'users_list' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/users') }}">
                            <i class="fa fa-user-circle-o"></i><span>Users</span>
                        </a>
                    </li>
                @endif
                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_admins'))
                    <li <?= isset($sub_menu) && $sub_menu == 'admin_users_list' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/admin_users') }}">
                            <i class="fa fa-user-md"></i><span>Admins</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    <!--merchants-->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_merchant') || Common::has_permission(\Auth::guard('admin')->user()->id, 'view_merchant_payment'))
        <li <?= $menu == 'merchant' ? ' class="active treeview"' : 'treeview'?> >
            <a href="#">
                <i class="glyphicon glyphicon-user"></i><span>Merchants</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_merchant'))
                    <li <?= isset($sub_menu) && $sub_menu == 'merchant_details' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/merchants') }}">
                            <i class="fa fa-user-circle-o"></i><span>Merchants</span>
                        </a>
                    </li>
                @endif

                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_merchant_payment'))
                    <li <?= isset($sub_menu) && $sub_menu == 'merchant_payments' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/merchant_payments') }}">
                            <i class="fa fa-money"></i><span>Merchant Payments</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    <!-- transactions -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_transaction'))
        <li <?= $menu == 'transactions' ? ' class="active treeview"' : 'treeview'?>>
            <a href="{{ url('admin/transactions') }}"><i class="fa fa-history"></i><span>Transactions</span></a>
        </li>
    @endif

    <!-- deposits -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_deposit'))
        <li <?= isset($menu) && $menu == 'deposits' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/deposits') }}"><i class="fa fa-arrow-down"></i><span>Deposits</span></a>
        </li>
    @endif

    <!-- Payouts -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_withdrawal'))
        <li <?= isset($menu) && $menu == 'withdrawals' ? ' class="active"' : ''?>>
            <a href="{{ url('admin/withdrawals') }}"><i class="fa fa-arrow-up"></i><span>Payouts</span></a>
        </li>
    @endif

    <!-- transfers -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_transfer'))
        <li <?= isset($menu) && $menu == 'transfers' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/transfers') }}"><i class="fa fa-exchange"></i><span>Transfers</span></a>
        </li>
    @endif

    <!-- exchanges -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_exchange'))
        <li <?= isset($menu) && $menu == 'exchanges' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/exchanges') }}"><i class="fa fa-money"></i><span>Currency Exchange</span></a>
        </li>
    @endif

    <!-- vouchers
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_voucher'))
        <li <?= isset($menu) && $menu == 'vouchers' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/vouchers') }}"><i class="fa fa-diamond"></i><span>Vouchers</span></a>
        </li>
    @endif
    -->

    <!-- request_payments -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_request_payment'))
        <li <?= isset($menu) && $menu == 'request_payments' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/request_payments') }}"><i class="fa fa-calculator"></i><span>Request Payments</span></a>
        </li>
    @endif

    <!-- revenues -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_revenue'))
        <li <?= isset($menu) && $menu == 'revenues' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/revenues') }}"><i class="fa fa-book"></i><span>Revenues</span></a>
        </li>
    @endif

    <!-- Disputes -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_disputes'))
        <li <?= isset($menu) && $menu == 'dispute' ? ' class="active"' : ''?> >
            <a href="{{url('admin/disputes')}}"><i class="fa fa-ticket"></i><span>Disputes</span></a>
        </li>
    @endif

    <!-- Tickets -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_tickets'))
        <li <?= isset($menu) && $menu == 'ticket' ? ' class="active"' : ''?> >
            <a href="{{url('admin/tickets/list')}}"><i class="fa fa-spinner"></i><span>Tickets</span></a>
        </li>
    @endif

    <!-- email_template -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_email_template'))
        <li <?= $menu == 'email_template' ? ' class="active treeview"' : 'treeview'?> >
            <a href="{{url('admin/template/17')}}">
                <i class="fa fa-newspaper-o"></i><span>Email Templates</span>
            </a>
        </li>
    @endif

    <!-- sms_template -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_sms_template'))
        <li <?= $menu == 'sms_template' ? ' class="active treeview"' : 'treeview'?> >
            <a href="{{url('admin/sms-template/21')}}">
                <i class="glyphicon glyphicon-phone"></i><span>SMS Templates</span>
            </a>
        </li>
    @endif

    <!-- activity_logs -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_activity_log'))
        <li <?= isset($menu) && $menu == 'activity_logs' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/activity_logs') }}"><i class="fa fa-eye"></i><span>Activity Logs</span></a>
        </li>
    @endif


    <!--verifications-->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_identity_verfication') || Common::has_permission(\Auth::guard('admin')->user()->id, 'view_address_verfication'))
        <li <?= $menu == 'proofs' ? ' class="active treeview"' : 'treeview'?> >
            <a href="#">
                <i class="glyphicon glyphicon-check"></i><span>Verifications</span>
                <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
            </a>
            <ul class="treeview-menu">
                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_identity_verfication'))
                    <li <?= isset($sub_menu) && $sub_menu == 'identity-proofs' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/identity-proofs') }}">
                            <i class="fa fa-user-circle-o"></i><span>Identity Verification</span>
                        </a>
                    </li>
                @endif

                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_address_verfication'))
                    <li <?= isset($sub_menu) && $sub_menu == 'address-proofs' ? ' class="active"' : ''?> >
                        <a href="{{ url('admin/address-proofs') }}">
                            <i class="fa fa-address-book"></i><span>Address Verification</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    <!-- Currencies & Fees -->
    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_currency'))
        <li <?= isset($menu) && $menu == 'currency' ? ' class="active"' : ''?> >
            <a href="{{ url('admin/settings/currency') }}"><i class="fa fa-money"></i><span>Currencies</span></a>
        </li>
    @endif


    <!-- settings -->
    <li <?= $menu == 'settings' ? ' class="active treeview"' : 'treeview'?> >
        <a href="{{ url('admin/settings') }}">
            <i class="fa fa-wrench"></i><span>Settings</span>
            <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
        </a>
    </li>
</ul>
