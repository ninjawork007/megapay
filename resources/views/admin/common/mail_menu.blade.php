<div class="box box-primary">

  {{-- normal template --}}
  <div class="box-header with-border">
    <h3 class="box-title underline">Email Templates</h3>
  </div>
  <div class="box-body no-padding" style="display: block;">
    <ul class="nav nav-pills nav-stacked">

      <li {{ isset($list_menu) &&  $list_menu == 'menu-17' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/17")}}">Email Verification</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-19' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/19")}}">2-Factor Authentication</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-21' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/21")}}">Identity/Address Verification</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-18' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/18")}}">Password Reset</a>
      </li>

      {{-- <li {{ isset($list_menu) &&  $list_menu == 'menu-' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/3")}}">Bank Transfer Payments</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'menu-1' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/1")}}">Transferred Payments</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-2' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/2")}}">Received Payments</a>
      </li>

     {{--  <li {{ isset($list_menu) &&  $list_menu == 'menu-3' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/3")}}">Voucher Activation</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'menu-4' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/4")}}">Request Payment Creation</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-5' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/5")}}">Request Payment Acceptance</a>
      </li>
      {{-- <li {{ isset($list_menu) &&  $list_menu == 'menu-9' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/9")}}">Payout</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'menu-11' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/11")}}">Ticket</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-12' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/12")}}">Ticket Reply</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-13' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/13")}}">Dispute Reply</a>
      </li>

    </ul>
  </div>
</div>

<div class="box box-primary">
{{-- Status template --}}
  <div class="box-header with-border">
    <h3 class="box-title underline">Email Templates of Admin actions</h3>
  </div>
  <div class="box-body no-padding" style="display: block;">
    <ul class="nav nav-pills nav-stacked">

      <li {{ isset($list_menu) &&  $list_menu == 'menu-14' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/14")}}">Merchant Payment</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'menu-10' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/10")}}">Payout</a>
      </li>

     {{--  <li {{ isset($list_menu) &&  $list_menu == 'menu-7' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/7")}}">Bank Transfers</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'menu-6' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/6")}}">Transfers</a>
      </li>

      {{-- <li {{ isset($list_menu) &&  $list_menu == 'menu-7' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/7")}}">Vouchers</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'menu-8' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/8")}}">Request Payments (Success/Refund)</a>
      </li>
      <li {{ isset($list_menu) &&  $list_menu == 'menu-16' ? 'class=active' : ''}} >
        <a href="{{ URL::to("admin/template/16")}}">Request Payments (Cancel/Pending)</a>
      </li>

    </ul>
  </div>
  </div>