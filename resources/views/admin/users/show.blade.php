@extends('admin.layouts.master')

@section('title', 'Users Details')

@section('content_header')
@endsection

@section('page_content')
<div class="box">
    <div class="box-body">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">User ID: {{ $users->id }}</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
	                              <tr>
	                                <td>{{ $users->first_name }}</td>
	                                <td>{{ $users->last_name }}</td>
	                                <td>{{ $users->phone }}</td>
	                                <td>{{ $users->email }}</td>
	                              </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('extra_body_scripts')
<script type="text/javascript">
</script>
@endpush
