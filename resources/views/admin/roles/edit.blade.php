@extends('admin.layouts.master')

@section('title', 'Edit Role')

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
            <h3 class="box-title">Edit Role</h3>
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
          <form method="POST" action="{{ url('admin/settings/edit_role/'. $result->id) }}" class="form-horizontal" id="roles_edit_form">
              {{ csrf_field() }}

              <div class="box-body">

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Name</label>
                    <div class="col-sm-6">
                      <input type="text" name="name" class="form-control" value="{{ $result->name }}" placeholder="Name" id="name">
                      <span class="text-danger">{{ $errors->first('name') }}</span>
                      <span id="name-error"></span>
                      <span id="name-ok" class="text-success"></span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Display Name</label>
                    <div class="col-sm-6">
                      <input placeholder="Display Name" type="text" class="form-control" name="display_name" value="{{ $result->display_name }}" >
                      <span class="text-danger">{{ $errors->first('display_name') }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Description</label>
                    <div class="col-sm-6">
                      <textarea placeholder="Description" rows="3" class="form-control" name="description">{{ $result->description }}</textarea>
                      <span class="text-danger">{{ $errors->first('description') }}</span>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group">
                      <div class="col-md-8 col-md-offset-2">
                          <div class="table-responsive">
                              <table class="table table-bordered">
                                <thead>
                                  <tr>
                                    <th>Permissions</th>
                                    <th>View</th>
                                    <th>Add</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                  </tr>
                                </thead>

                                <tbody>

                                  @php
                                    $i=0;
                                  @endphp

                                  @foreach ($permissions as $key => $value)

                                    <tr data-rel="{{$i}}">

                                      <td>{{ $key }}</td>

                                      @foreach ($value as $i => $v)

                                          <input type="hidden" value="{{ $v['user_type'] }}" name="user_type" id="user_type">
                                          <input type="hidden" value="{{ $result->id }}" name="id" id="id">

                                          @if (isset($v['display_name']))
                                            <td>

                                              <label class="checkbox-container">
                                                <input type="checkbox" name="permission[]" id="{{'view_'.$i}}" value="{{ $v['id'] }}"  {{ in_array( $v['id'], $stored_permissions) ? 'checked' : '' }}
                                            class="{{ ($i % 4 == 0) ? 'view_checkbox' :'other_checkbox' }}">

                                                <span class="checkmark"></span>
                                              </label>

                                            </td>
                                          @else
                                            <td></td>
                                          @endif
                                      @endforeach
                                    </tr>

                                    @php
                                        $i++;
                                    @endphp

                                  @endforeach
                                </tbody>
                              </table>
                              <div id="error-message"></div>
                          </div>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="box-footer">
                <a class="btn btn-danger" href="{{ url('admin/settings/roles') }}">Cancel<a>
                <button type="submit" class="btn btn-primary pull-right">Update</button>
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

  $(document).on('change','.other_checkbox',function()
  {
      var tr_id        = $(this).closest('tr').attr('data-rel');
      var fieldInputId = 'view_'+tr_id;
      $("#"+fieldInputId).prop("checked",true);
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

  $('#roles_edit_form').validate({
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
      var id = $('#id').val();
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
              'id': id,
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
              emptyName();
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
