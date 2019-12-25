@extends('admin.layouts.master')

@section('title', 'Add User Group')

@section('head_style')
  <!-- custom-checkbox -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/css/custom-checkbox.css') }}">
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
          <h3 class="box-title">Add User Group</h3>
        </div>

        <!-- form start -->
        <form method="POST" action="{{ url('admin/settings/add_user_role') }}" class="form-horizontal" enctype="multipart/form-data" id="group_add_form">
          {{ csrf_field() }}

          <div class="box-body">
            <div class="form-group">
              <label class="col-sm-3 control-label" for="exampleFormControlInput1">Name</label>
              <div class="col-sm-6">
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Name" id="name">
                @if($errors->has('name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('name') }}</strong>
                </span>
                @endif
                <span id="name-error"></span>
                <span id="name-ok" class="text-success"></span>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="exampleFormControlInput1">Display Name</label>
              <div class="col-sm-6">
                <input type="text" name="display_name" class="form-control" value="{{ old('display_name') }}" placeholder="Display Name" id="display_name">
                @if($errors->has('display_name'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('display_name') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="exampleFormControlInput1">Description</label>
              <div class="col-sm-6">
                <textarea name="description" placeholder="Description" rows="3" class="form-control" value="{{ old('description') }}" id="description"></textarea>
                @if($errors->has('description'))
                <span class="help-block">
                  <strong class="text-danger">{{ $errors->first('description') }}</strong>
                </span>
                @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="exampleFormControlInput1">Default</label>
              <div class="col-sm-6">
                <select class="select2" name="default" id="default">
                    <option value='No'>No</option>
                    <option value='Yes'>Yes</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" for="exampleFormControlInput1"></label>

              <div class="col-sm-5">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>Permissions</th>
                        <th>Action</th>
                      </tr>
                    </thead>

                    <tbody>
                      @php $arr=['Transaction','Dispute','Ticket','Settings'] @endphp

                      @if (isset($permissions))
                        @foreach ($permissions as $permission)
                          @if(in_array($permission->group,$arr))

                              <input style="display: none" type="checkbox" name="permission[]"
                                     id="permission" value="{{$permission->id}}" checked>
                          @else
                              <tr>
                                <input type="hidden" value="{{ $permission->user_type }}" name="user_type" id="user_type">

                                <td>{{ $permission->group }}</td>
                                <td>

                                  <label class="checkbox-container">
                                    <input type="checkbox" name="permission[]" value="{{ $permission->id }}">
                                    <span class="checkmark"></span>
                                  </label>

                                </td>
                              </tr>
                          @endif
                        @endforeach
                      @endif

                    </tbody>
                  </table>
                  <div id="error-message"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="box-footer">
            <a class="btn btn-danger" href="{{ url('admin/settings/user_role') }}">Cancel</a>
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
     errorPlacement: function (error, element) {
      if (element.prop('type') === 'checkbox') {
        $('#error-message').html(error);
      } else {
        error.insertAfter(element);
      }
    }
  });

  $('#group_add_form').validate({
    rules: {
      name: {
        required: true,
        letters_with_spaces: true,
      },
      display_name: {
        required: true,
        letters_with_spaces: true,
      },
      description: {
        required: true,
        letters_with_spaces: true,
      },
      "permission[]": {
        required: true,
        minlength: 1
      },
    },
    messages: {
      "permission[]": {
        required: "Please select at least one checkbox!",
      },
    },
  });

  // Validate Role Name via Ajax
$(document).ready(function()
{
    $("#name").on('input', function(e)
    {
      var name = $('#name').val();
      var user_type = $('#user_type').val();
      $.ajax({
          headers:
          {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          method: "POST",
          url: SITE_URL+"/admin/settings/roles/duplicate-role-check",
          dataType: "json",
          data: {
              'name': name,
              'user_type': user_type,
          }
      })
      .done(function(response)
      {
          // console.log(response);
          if (response.status == true)
          {
              emptyName();
              $('#name-error').show();
              $('#name-error').addClass('error').html(response.fail).css("font-weight", "bold");
              $('form').find("button[type='submit']").prop('disabled',true);
          }
          else if (response.status == false)
          {
              $('#name-error').html('');
              $('form').find("button[type='submit']").prop('disabled',false);
          }

          function emptyName() {
              if( name.length === 0 )
              {
                  $('#name-error').html('');
              }
          }
      });
    });
});

</script>

@endpush
