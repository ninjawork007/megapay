@extends('admin.layouts.master')

@section('title', 'Fees')

@section('head_style')
    <!-- dataTables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
  <!-- Main content -->
    <div class="row">
      <div class="col-md-3 settings_bar_gap">
          @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
          <div class="box box_info">
                <div class="box-header">
                  <h3 class="box-title">Manage Fees</h3>

                  @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'add_fees'))
                    <div style="float:right;"><a class="btn btn-success" href="{{ url('admin/settings/add_fees') }}">Add Fees</a></div>
                  @endif
                </div>
                <hr>
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table class="table text-center" id="fees">
                        <thead>
                          <tr>
                              <th>Transaction Type</th>
                              <th>Charge Percentage</th>
                              <th>Fixed Charge</th>
                              <th>Method</th>
                              <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($fees as $result)
                            <tr>
                              <td>{{$result->transaction_type}}</td>

                              <td>{{ decimalFormat($result->charge_percentage) }}</td>

                              <td>{{ decimalFormat($result->charge_fixed) }}</td>

                              @if(empty($result->payment_method))
                                <td>{{ '-' }}</td>
                              @else
                                <td>{{ $result->payment_method->name }}</td>
                              @endif

                              <td>
                                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_fees'))
                                  <a class="btn btn-xs btn-primary" href="{{url('admin/settings/edit_fees/'.$result->id)}}"><i class="glyphicon glyphicon-edit"></i></a>
                                @endif

                                @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_fees'))
                                  <a class="btn btn-xs btn-danger delete-warning" href="{{url('admin/settings/delete_fees/'.$result->id)}}"><i class="glyphicon glyphicon-trash"></i></a>
                                @endif
                              </td>
                            </tr>
                          @empty
                            <h3 class="panel-title text-center">No Fee Found!</h3>
                          @endforelse

                        </tbody>
                    </table>
                </div>
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
    $("#fees").DataTable({
      "order": [],
      "columnDefs": [ {
        "targets": 4,
        "className": "dt-center",
        "orderable": false
        } ],
        "language": '{{Session::get('dflt_lang')}}',
        "pageLength": '{{Session::get('row_per_page')}}'
    });
  });
</script>

@endpush