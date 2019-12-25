@extends('admin.layouts.master')

@section('title', 'Database Backups')

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
    <div class="col-xs-9">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">Manage Databases</h3>
            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'add_database_backup') && $is_demo == false)
              <div style="float:right;"><a class="btn btn-success" href="{{ url('admin/backup/save') }}">New Backup</a></div>
            @endif
        </div>
        <hr>
        <!-- /.box-header -->
        <div class="box-body table-responsive">
          {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
        </div>
      </div>
    </div>
  </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/backend/DataTables_latest/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/backend/DataTables_latest/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

{!! $dataTable->scripts() !!}
@endpush

