@extends('admin.layouts.master')

@section('title', 'Edit Language')

@section('head_style')
  <!-- sweetalert -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/sweetalert/sweetalert.css')}}">
@endsection

@section('page_content')
  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <!-- Horizontal Form -->
      <div class="box box-info">
        <div class="box-header with-border text-center">
          <h3 class="box-title">Edit Language</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url('admin/settings/edit_language/'.$result->id) }}" class="form-horizontal" enctype="multipart/form-data" id="edit_language_form">
          {{ csrf_field() }}

          <input type="hidden" value="{{ $result->id }}" name="id" id="id">

          <div class="box-body">
            <div class="form-group">
              <label class="col-sm-3 control-label" for="name">Name</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control" value="{{ $result->name }}" placeholder="name" id="name">
                @if($errors->has('name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="short_name">Short Name</label>
              <div class="col-sm-6">
                <input type="text" name="short_name" class="form-control" value="{{ $result->short_name }}" placeholder="short name" id="short_name">
                @if($errors->has('short_name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('short_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label for="inputEmail3" class="col-sm-3 control-label">Flag</label>
              <div class="col-sm-6">
                <input type="file" name="flag" class="form-control input-file-field" data-rel="{{ isset($result->flag) ? $result->flag : '' }}" id="flag"
                value="{{ isset($result->flag) ? $result->flag : '' }}">
                <strong class="text-danger">{{ $errors->first('flag') }}</strong>

                @if (isset($result->flag))
                  <div class="setting-img">
                    <div class="img-wrap">
                        <img src='{{ url('public/uploads/languages-flags/'.$result->flag) }}'  class="img-responsive">
                    </div>
                    <span class="remove_img_preview" id="flag_preview"></span>
                  </div>
                @else
                  <img src='{{ url('public/uploads/userPic/default-image.png') }}' width="120" height="80" class="img-responsive">
                @endif
              </div>
            </div>

            <div class="form-group">
                <label class="col-sm-3 control-label" for="status">Status</label>
                <div class="col-sm-6">
                    <select class="select2" name="status" id="status">
                        <option value='Active' {{ $result->status == 'Active' ? 'selected':"" }}>Active</option>
                        <option value='Inactive' {{ $result->status == 'Inactive' ? 'selected':"" }}>Inactive</option>
                    </select>
                </div>
            </div>
          </div>

          <div class="box-footer">
            <a class="btn btn-danger" href="{{ url('admin/settings/language') }}">Cancel</a>
            <button type="submit" class="btn btn-primary pull-right">&nbsp; Update &nbsp;</button>
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

  $.validator.setDefaults({
      highlight: function(element) {
        $(element).parent('div').addClass('has-error');
      },
      unhighlight: function(element) {
       $(element).parent('div').removeClass('has-error');
     },
  });

  $('#edit_language_form').validate({
    rules: {
      name: {
        required: true,
      },
      short_name: {
        required: true,
        maxlength: 2,
        lettersonly: true,
      },
      flag: {
        extension: "png|jpg|jpeg|gif|bmp",
      },
    },
    messages: {
      short_name: {
        lettersonly: "Please enter letters only.",
      },
      flag: {
        extension: "Please select (png, jpg, jpeg, gif or bmp) file!"
      },
    },
  });

  $(document).ready(function()
  {
    $('#flag_preview').click(function()
    {
      var flag = $('#flag').attr('data-rel');
      var language_id = $('#id').val();

      if(flag)
      {
        $.ajax(
        {
          headers:
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type : "POST",
          url : SITE_URL+"/admin/settings/language/delete-flag",
          async : false,
          data: {
            'flag' : flag,
            'language_id' : language_id,
          },
          dataType : 'json',
          success: function(reply)
          {
            if (reply.success == 1){
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
