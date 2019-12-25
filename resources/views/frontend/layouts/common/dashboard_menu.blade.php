<div class="col-md-4">
    <div class="menu-list">
        <ul>
            <li class="{{ isset($menu) && ( $menu == 'dashboard' ) ? 'active' : '' }}"><a href="{{url('dashboard')}}">Dashboard</a></li>

            <li class="{{ isset($menu) && ( $menu == 'transactions' ) ? 'active' : '' }}"><a href="{{url('transactions')}}">Transactions</a></li>

            <li class="{{ isset($menu) && ( $menu == 'transfer' ) ? 'active' : '' }}"><a href="{{url('moneytransfer')}}">Money transfer</a></li>

            <li class="{{ isset($menu) && ( $menu == 'exchanges' ) ? 'active' : '' }}"><a href="{{url('exchanges')}}">Currency exchange</a></li>
            
            <li class="{{ isset($menu) && ( $menu == 'voucher' ) ? 'active' : '' }}"><a href="{{url('vouchers')}}">Voucher</a></li>

            <li class="{{ isset($menu) && ( $menu == 'request_payment' ) ? 'active' : '' }}"><a href="{{url('request_payments')}}">Request payment</a></li>

            <li class="{{ isset($menu) && ( $menu == 'merchant' ) ? 'active' : '' }}"><a href="{{url('merchants')}}">Merchants</a></li>

            <li class="{{ isset($menu) && ( $menu == 'merchant_payment' ) ? 'active' : '' }}"><a href="{{url('merchant/payments')}}">Merchant Payments</a></li>

            <li class="{{ isset($menu) && ( $menu == 'payouts' ) ? 'active' : '' }}"><a href="{{url('payouts')}}">Payouts</a></li>


            <li class="{{ isset($menu) && ( $menu == 'dispute' ) ? 'active' : '' }}"><a href="{{url('disputes')}}">Disputes</a></li>

            <li class="{{ isset($menu) && ( $menu == 'ticket' ) ? 'active' : '' }}"><a href="#">Tickets</a></li>
            
            <li class="{{ isset($menu) && ( $menu == 'account_setting' ) ? 'active' : '' }}"><a href="#">Account settings</a></li>
        </ul>
    </div>
</div>