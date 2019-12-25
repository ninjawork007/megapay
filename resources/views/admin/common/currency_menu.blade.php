<div class="box box-primary">

  <div class="box-header with-border">
    <h3 class="box-title underline">Transaction Type</h3>
  </div>
  <div class="box-body no-padding" style="display: block;">
    <ul class="nav nav-pills nav-stacked">

      <li  {{ isset($list_menu) &&  $list_menu == 'deposit' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/deposit/'.$currency->id) }}'>Deposit</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'withdrawal' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/withdrawal/'.$currency->id) }}'>Payout</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'transfer' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/transfer/'.$currency->id) }}'>Transfer</a>
      </li>

      <li {{ isset($list_menu) &&  $list_menu == 'exchange' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/exchange/'.$currency->id) }}'>Exchange</a>
      </li>

      {{-- <li {{ isset($list_menu) &&  $list_menu == 'voucher' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/voucher/'.$currency->id) }}'>Voucher</a>
      </li> --}}

      <li {{ isset($list_menu) &&  $list_menu == 'request_payment' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/request_payment/'.$currency->id) }}'>Request Payment</a>
      </li>


{{--       <li {{ isset($list_menu) &&  $list_menu == 'bank_transfer' ? 'class=active' : ''}} >
        <a data-spinner="true" href='{{ url('admin/settings/feeslimit/bank_transfer/'.$currency->id) }}'>Bank Transfer</a>
      </li> --}}


    </ul>
  </div>
</div>

