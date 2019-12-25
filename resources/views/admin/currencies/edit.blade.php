@extends('admin.layouts.master')
@section('title', 'Edit Currency')

@section('head_style')
  <!-- sweetalert -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/sweetalert/sweetalert.css')}}">
@endsection


@section('page_content')
<!-- Main content -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border text-center">
              <h3 class="box-title">Edit Currency</h3>
            </div>

            <form action="{{ url('admin/settings/edit_currency/'.$result->id) }}" method="POST" class="form-horizontal" enctype="multipart/form-data" id="edit_currency_form">
                {!! csrf_field() !!}

                <input type="hidden" value="{{ $result->id }}" name="id" id="id">
                <input type="hidden" value="{{ $result->default }}" name="default_currency" id="default_currency">

                <!-- box-body -->
                <div class="box-body">
                  {{-- Name --}}
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Name</label>
                    <div class="col-sm-6">
                      <input type="text" name="name" class="form-control" value="{{ $result->name }}" placeholder="Name" id="name">
                      {{-- <p class="form-control-static">{{ $result->name }}</span></p> --}}
                      <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Code</label>
                    <div class="col-sm-6">
                      <input type="text" name="code" class="form-control" value="{{ $result->code }}" placeholder="Code" id="code">
                      <span class="text-danger">{{ $errors->first('code') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Symbol</label>
                    <div class="col-sm-6">
                      <input type="text" name="symbol" class="form-control" value="{{ $result->org_symbol }}" placeholder="Symbol" id="symbol">
                      <span class="text-danger">{{ $errors->first('symbol') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Exchange Rate</label>
                    <div class="col-sm-6">
                      <input type="text" name="rate" class="form-control" value="{{ $result->rate }}" placeholder="Rate" id="rate">
                      <span class="text-danger">{{ $errors->first('rate') }}</span>
                    </div>
                  </div>

                  {{-- Logo --}}
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-3 control-label">Logo</label>
                    <div class="col-sm-6">
                      <input type="file" name="logo" class="form-control input-file-field" data-rel="{{ isset($result->logo) ? $result->logo : '' }}" id="logo"
                      value="{{ isset($result->logo) ? $result->logo : '' }}">
                      <span class="text-danger">{{ $errors->first('logo') }}</span>

                      @if ($result->logo)
                        <div class="setting-img">
                          <div class="img-wrap">
                              <img src='{{ url('public/uploads/currency_logos/'.$result->logo) }}'  class="img-responsive">
                          </div>
                          <span class="remove_img_preview"></span>
                        </div>
                      @else
                        <img src='{{ url('public/user_dashboard/images/favicon.png') }}' width="70" height="50" class="img-responsive">
                      @endif
                    </div>
                  </div>

                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="inputEmail3">Exchange From</label>
                      <div class="col-sm-6">
                        <select class="select2" name="exchange_from" id="exchange_from">
                            <option value='local' {{ isset($result->exchange_from) && $result->exchange_from == 'local' ? 'selected':"" }}>local</option>
                            <option value='api' {{ isset($result->exchange_from) && $result->exchange_from == 'api' ? 'selected':"" }}>api</option>
                        </select>
                        <span class="text-danger">{{ $errors->first('exchange_from') }}</span>
                      </div>
                  </div>


                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="inputEmail3">Status</label>
                      <div class="col-sm-6">

                        @if ($result->default == 1)
                          <p class="form-control-static"><span class="label label-danger">Staus Change Disallowed </span></p><p><span class="label label-warning">Default Currency</span></p>

                        @else
                          <select class="select2" name="status" id="status">
                              <option value='Active' {{ $result->status == 'Active' ? 'selected':"" }}>Active</option>
                              <option value='Inactive' {{ $result->status == 'Inactive' ? 'selected':"" }}>Inactive</option>
                          </select>
                          <span class="text-danger">{{ $errors->first('status') }}</span>
                        @endif
                      </div>
                  </div>
                  <!-- /.box-body -->

                  <!-- box-footer -->
                  <div class="box-footer">
                    <a href="{{ url("admin/settings/currency") }}" class="btn btn-danger btn-flat">Cancel</a>
                    <button class="btn btn-primary btn-flat pull-right" type="submit">Update</button>
                  </div>
                  <!-- /.box-footer -->
            </form>

        </div>
    </div>
</div>
<!-- /.box -->

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/js/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
<!-- /dist -->

<!-- sweetalert -->
<script src="{{ asset('public/backend/sweetalert/sweetalert.min.js')}}" type="text/javascript"></script>

<script type="text/javascript">

    $(function () {
        $(".select2").select2({
        });
    });

    jQuery.validator.addMethod("letters_with_spaces", function(value, element)
    {
        return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
    }, "Please enter letters only!");

    $.validator.addMethod("select_dropdown", function(value, element, arg){
      return arg !== value;
    }, "Value cannot be null.");


    $.validator.setDefaults({
        highlight: function(element) {
           $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
    });

    $('#edit_currency_form').validate({
        rules: {
            name: {
                required: true,
                // letters_with_spaces: true,
            },
            code: {
                required: true,
                // letters_with_spaces: true,
            },
            symbol: {
                required: true,
            },
            rate: {
                required: true,
                number: true,
                // min: 0.01,
            },
            logo: {
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          rate: {
            min: "Please enter values greater than 0!"
          },
          logo: {
            extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
          },
        },
    });

    $(document).ready(function(){
      $('.remove_img_preview').click(function(){
        var image = $('#logo').attr('data-rel');
        // var image = $('#logo').data('rel');
        var currency_id = $('#id').val();

        if(image)
        {
          $.ajax(
          {
            headers:
            {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type : "POST",
            url : SITE_URL+"/admin/settings/currency/delete-currency-logo",
            async : false,
            data: {
              'logo' : image,
              'currency_id' : currency_id,
            },
            dataType : 'json',
            success: function(reply)
            {
              if (reply.success == 1){
              // alert(reply.message);
              // location.reload();
              swal({title: "Deleted!", text: reply.message, type: "success"},
                function(){
                   location.reload();
                }
              );

              }else{
                  alert(reply.message);
                  location.reload();
              }
            }
          });
        }
      });
    });
</script>

@endpush
