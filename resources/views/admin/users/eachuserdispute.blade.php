@extends('admin.layouts.master')

@section('title', 'Disputes')

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
                <li>
                  <a href="{{url("admin/users/wallets/$users->id")}}">Wallets</a>
                </li>
                <li>
                  <a href="{{url("admin/users/tickets/$users->id")}}">Tickets</a>
                </li>
                <li class="active">
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
                    <table class="table table-hover" id="eachuserdispute">
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Claimant</th>
                            <th>Defendant</th>
                            <th>Transaction ID</th>
                            <th>Title</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($disputes)
                            @foreach($disputes as $dispute)
                                <tr>
                                    <td>{{ dateFormat($dispute->created_at) }}</td>

                                    <td><a href="{{ url('admin/users/edit/'. $dispute->claimant->id) }}">{{ isset($dispute->claimant) ? $dispute->claimant->first_name.' '.$dispute->claimant->last_name :"-" }}</a></td>

                                    <td><a href="{{ url('admin/users/edit/'. $dispute->defendant->id) }}">{{ isset($dispute->defendant) ? $dispute->defendant->first_name .' '.$dispute->defendant->last_name :"-" }}</a></td>

                                    <td>
                                        @if (isset($dispute->transaction))
                                            <a href="{{ url('admin/transactions/edit/'.$dispute->transaction->id) }}" target="_blank">{{ $dispute->transaction->uuid }}</a>
                                        @else
                                            {{ 'Not Found' }}
                                        @endif
                                    </td>

                                    <td><a href="{{ url('admin/dispute/discussion/'.$dispute->id) }}">{{ $dispute->title }}</a></td>

                                    @if($dispute->status=='Open')
                                      <td><span class="label label-primary">Open</span></td>
                                    @else
                                      <td><span class="label label-success">Closed</span></td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            No Dispute Found!
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('admin.layouts.partials.message_boxes')
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
      $("#eachuserdispute").DataTable({
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
