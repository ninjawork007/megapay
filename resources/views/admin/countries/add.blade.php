@extends('admin.layouts.master')

@section('title', 'Add Country')

@section('page_content')

  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border text-center">
          <h3 class="box-title">Add Country</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url('admin/settings/add_country') }}" class="form-horizontal" id="add_country_form">
          {{ csrf_field() }}

          <div class="box-body">
            <div class="form-group">
              <label class="col-sm-3 control-label" for="short_name">Short Name</label>
              <div class="col-sm-6">
                <input type="text" name="short_name" class="form-control" value="{{ old('short_name') }}" placeholder="short name" id="short_name">
                @if($errors->has('short_name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('short_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="name">Long Name</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="long name" id="name">
                @if($errors->has('name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="iso3">ISO3</label>
              <div class="col-sm-6">
                <input type="text" name="iso3" class="form-control" value="{{ old('iso3') }}" placeholder="iso3" id="iso3">
                @if($errors->has('iso3'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('iso3') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="number_code">Number Code</label>
              <div class="col-sm-6">
                <input type="text" name="number_code" class="form-control" value="{{ old('number_code') }}" placeholder="number code" id="number_code">
                @if($errors->has('number_code'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('number_code') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="phone_code">Phone Code</label>
              <div class="col-sm-6">
                <input type="text" name="phone_code" class="form-control" value="{{ old('phone_code') }}" placeholder="phone code" id="phone_code">
                @if($errors->has('phone_code'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('phone_code') }}</strong>
                </span>
                @endif
              </div>
            </div>

          </div>

          <div class="box-footer">
            <a class="btn btn-danger" href="{{ url('admin/settings/country') }}">Cancel</a>
            <button type="submit" class="btn btn-primary pull-right">&nbsp; Add &nbsp;</button>
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

<script type="text/javascript">

  jQuery.validator.addMethod("letters_with_spaces", function(value, element)
  {
    return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
  }, "Please enter letters only!");

  $.validator.setDefaults({
      highlight: function(element) {
        $(element).parent('div').addClass('has-error');
      },
      unhighlight: function(element) {
       $(element).parent('div').removeClass('has-error');
     },
  });

  $('#add_country_form').validate({
    rules: {
      short_name: {
        required: true,
        maxlength: 2,
        lettersonly: true,
      },
      name: {
        required: true,
        // letters_with_spaces: true,
      },
      iso3: {
        required: true,
        maxlength: 3,
        lettersonly: true,
      },
      number_code: {
        required: true,
        digits: true
      },
      phone_code: {
        required: true,
        digits: true
      },
    },
    messages: {
      short_name: {
        lettersonly: "Please enter letters only!",
      },
      iso3: {
        lettersonly: "Please enter letters only!",
      },
    },
  });

</script>
@endpush