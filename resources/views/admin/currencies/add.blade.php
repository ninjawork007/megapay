@extends('admin.layouts.master')
@section('title', 'Add Currency')

@section('page_content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border text-center">
              <h3 class="box-title">Add Currency</h3>
            </div>

            <form action="{{ url('admin/settings/add_currency') }}" method="post" class="form-horizontal" enctype="multipart/form-data" id="add_currency_form">
                {!! csrf_field() !!}

                <!-- box-body -->
                <div class="box-body">
                  <!-- Name -->
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Name</label>
                    <div class="col-sm-6">
                      <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Name" id="name">
                      <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Code</label>
                    <div class="col-sm-6">
                      <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Code" id="code">
                      <span class="text-danger">{{ $errors->first('code') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Symbol</label>
                    <div class="col-sm-6">
                      <input type="text" name="symbol" class="form-control" value="{{ old('symbol') }}" placeholder="Symbol" id="symbol">
                      <span class="text-danger">{{ $errors->first('symbol') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputEmail3">Exchange Rate</label>
                    <div class="col-sm-6">

                      <input type="text" name="rate" class="form-control" value="{{ old('rate') }}" placeholder="Rate" id="rate">

                      <span class="text-danger">{{ $errors->first('rate') }}</span>
                    </div>
                  </div>

                  <!-- Logo -->
                  <div class="form-group">
                    <label for="inputEmail3" class="col-sm-3 control-label">Logo</label>
                    <div class="col-sm-6">
                      <input type="file" name="logo" class="form-control input-file-field">
                    </div>
                  </div>

                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="inputEmail3">Exchange From</label>
                      <div class="col-sm-6">
                          <select class="select2" name="exchange_from" id="exchange_from">
                              <option value='local'>local</option>
                              <option value='api'>api</option>
                          </select>
                      </div>
                  </div>


                  <div class="form-group">
                      <label class="col-sm-3 control-label" for="inputEmail3">Status</label>
                      <div class="col-sm-6">
                          <select class="select2" name="status" id="status">
                              <option value='Active'>Active</option>
                              <option value='Inactive'>Inactive</option>
                          </select>
                      </div>
                  </div>
                  <!-- /.box-body -->

                  <!-- box-footer -->
                  <div class="box-footer">
                    <a href="{{ url("admin/settings/currency") }}" class="btn btn-danger btn-flat">Cancel</a>
                    <button class="btn btn-primary btn-flat pull-right" type="submit">Submit</button>
                  </div>
                  <!-- /.box-footer -->
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- jquery.validate additional-methods -->
<script src="{{ asset('public/dist/js/jquery-validation-1.17.0/dist/additional-methods.min.js') }}" type="text/javascript"></script>
<!-- /dist -->

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

    $('#add_currency_form').validate({
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
                // min: 1,
            },
            logo: {
                extension: "png|jpg|jpeg|gif|bmp",
            },
        },
        messages: {
          rate: {
            min: "Please enter values greater than zero!"
          },
          logo: {
            extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
          }
        },
    });
</script>

@endpush
