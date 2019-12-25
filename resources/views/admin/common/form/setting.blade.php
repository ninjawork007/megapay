@extends('admin.layouts.master')

@section('title', $page_title)

@section('page_content')

  <div class="row">
      <div class="col-md-3 settings_bar_gap">

        @include('admin.common.settings_bar')

      </div>
      <div class="col-md-9">
        <!-- Horizontal Form -->

        <div class="box box-info">
          <div class="box-header with-border text-center">
            <h3 class="box-title">{{ $form_name or '' }}</h3>
              {{-- @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
              @endif --}}
          </div>

          <!-- form start -->
          <form id="{{ $form_id or ''}}"
            method="post"
            action="{{ $action or ''}}"
            class="form-horizontal {{ $form_class or '' }}" {{ isset($form_type) && $form_type == 'file'? "enctype=multipart/form-data":"" }}>
            {{ csrf_field() }}

              <div class="box-body">
                @foreach($fields as $field)
                  @include("admin.common.fields.".$field['type'], ['field' => $field])
                @endforeach
              </div>

              <div class="box-footer">

                {{-- <a class="btn btn-danger" href="{{ URL::previous() }}">Cancel</a> --}}
                <button type="submit" class="btn btn-primary pull-right">Submit</button>
              </div>
          </form>
        </div>
      </div>
  </div>

@endsection

@push('extra_body_scripts')
  <script type="text/javascript"></script>
@endpush
