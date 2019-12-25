@extends('admin.layouts.master')

@section('title', 'Wallets')

@section('head_style')
    <!-- dataTables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
    <div class="box">
       <div class="panel-body">
            <ul class="nav nav-tabs cus" role="tablist">
                <li>
                  <a href='{{url("admin/users/edit/$users->id")}}'>Profile</a>
                </li>

                <li>
                  <a href="{{url("admin/users/transactions/$users->id")}}">Transactions</a>
                </li>
                <li class="active">
                  <a href="{{url("admin/users/wallets/$users->id")}}">Wallets</a>
                </li>
                <li>
                  <a href="{{url("admin/users/tickets/$users->id")}}">Tickets</a>
                </li>
                <li>
                  <a href="{{url("admin/users/disputes/$users->id")}}">Disputes</a>
                </li>
           </ul>
          <div class="clearfix"></div>
       </div>
    </div>

    <h3>{{ $users->first_name.' '.$users->last_name }}</h3>

    <div class="box">
      <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-hover" id="eachuserwallet">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Balance</th>
                            <th>Currency</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($wallets)
                            @foreach($wallets as $wallet)
                                <tr>
                                    <td>{{ dateFormat($wallet->created_at) }}</td>

                                    <td>{{ formatNumber($wallet->balance) }}</td>

                                    <td>{{ $wallet->currency->code }}</td>

                                    @if ($wallet->is_default == 'Yes')
                                        <td><span class="label label-success">Yes</span></td>
                                    @elseif ($wallet->is_default == 'No')
                                        <td><span class="label label-danger">No</span></td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            No wallet Found!
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
      $("#eachuserwallet").DataTable({
            "order": [],
            "columnDefs": [
            {
                "className": "dt-center",
                "targets": "_all"
            }
            ],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
