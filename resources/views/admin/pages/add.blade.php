@extends('admin.layouts.master')

@section('title', 'Add Page')

@section('head_style')
  <!-- summernote -->
  <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/editor/summernote.css')}}">
@endsection

@section('page_content')
  <div class="row">
    <div class="col-md-3">
       @include('admin.common.settings_bar')
    </div>

    <div class="col-md-9">
      <div class="box box-default">
        <div class="box-body">
          <div class="row">
            <div class="col-md-10">
             <div class="top-bar-title padding-bottom">New Page</div>
            </div>
            <div class="col-md-2">
              <div class="top-bar-title padding-bottom">
              <a href="{{ url("admin/settings/pages") }}" class="btn btn-block btn-default btn-flat btn-border-orange">Pages</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="box">
      <div class="box-body">
        <!-- /.box-header -->
        <form action="{{url('admin/settings/page/store')}}" method="POST" id="page" class="form-horizontal" enctype="multipart/form-data">
           {{ csrf_field() }}

          <div class="box-body">
            <div class="form-group">
              <label class="col-sm-2 control-label required" for="inputEmail3">Name</label>
              <div class="col-sm-8">
                <input class="form-control" name="name" placeholder="Name" value="{{ old('name') }}" type="text" id="name">
                  @if ($errors->has('name'))
                      <span class="error">
                          <strong>{{ $errors->first('name') }}</strong>
                      </span>
                  @endif
              </div>
            </div>


            <div class="form-group">
              <label class="col-sm-2 control-label require" for="inputEmail3">Content</label>
              <div class="col-sm-8">
                <textarea class="form-control" name="content" placeholder="Content" rows="10" cols="80"  id="content"></textarea>
                  @if ($errors->has('content'))
                      <span class="error">
                          <strong>{{ $errors->first('content') }}</strong>
                      </span>
                  @endif
              </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label required">Position</label>
                <div class="col-sm-10">
                    <div class="checkbox">
                        <label style="margin-right: 15px">
                            <input type="checkbox" name="header" class="position" id="header">
                            Header
                        </label>
                        <label>
                            <input type="checkbox" name="footer" class="position" id="footer">
                            Footer
                        </label>
                    </div>
                    <div id="error-message"></div>
                </div>

            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label required">Status</label>
              <div class="col-sm-8">
                <select class="select2" name="status" id="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                  @if ($errors->has('status'))
                    <span class="error">
                        <strong>{{ $errors->first('status') }}</strong>
                    </span>
                  @endif
              </div>
            </div>
          </div>
          <!-- /.box-body -->

          <div class="box-footer">
            <a href="{{ url("admin/settings/pages") }}" class="btn btn-danger btn-flat">Cancel</a>
            <button class="btn btn-primary pull-right btn-flat">Submit</button>
          </div>
          <!-- /.box-footer -->
        </form>
        </div>
        <!-- /.box-body -->
      </div>
      <!-- /.nav-tabs-custom -->
    </div>
    <!-- /.col -->
  </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script src="{{ asset('public/dist/editor/summernote.js')}}" type="text/javascript"></script>

<script type="text/javascript">
    //summernote.js note script
    // $(function()
    $(window).on('load',function()
    {
        $(".note-group-select-from-files").hide();
        $('#content').summernote({
            tabsize: 2,
            height: 150,
            toolbar: [
              ['style', ['style']],
              ['font', ['bold', 'italic', 'underline']],
              ['fontname', ['fontname']],
              ['fontsize', ['fontsize']],
              ['color', ['color']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['table', ['table']],
              ['insert', ['link', 'hr','picture']]
            ],
        });
    });

    $(".select2").select2();

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    $('#page').validate({
        rules: {
            name: {
                required: true,
            },
            content:{
               required: true,
            },
        },
    });

    // Multiple Checkboxes Validation (on submit)
    $(document).ready(function()
    {
      $('form').submit(function()
      {
        checkPosition();
      });
    });

    // Multiple Checkboxes Validation (on change)
    $(document).on('change','.position',function()
    {
      checkPosition();
    });

    function checkPosition()
    {
      var checkedLength = $('input[type=checkbox]:checked').length;
      if(checkedLength > 1)
      {
        $('#error-message').html('');
        return true;
      }
      else
      {
        $('#error-message').addClass('error').html('Please check at least one position.').css("font-weight", "bold");
        return false;
      }
    }

    // $(document).ready(function()
    // {
    //   $('#page').bootstrapValidator({
    //     excluded: [':disabled'],
    //     feedbackIcons: {
    //         // valid: 'glyphicon glyphicon-ok',
    //         // invalid: 'glyphicon glyphicon-remove',
    //         // validating: 'glyphicon glyphicon-refresh'
    //     },
    //     fields: {
    //         name: {
    //             validators: {
    //                 notEmpty: {
    //                     message: 'The name is required.'
    //                 }
    //             }
    //         },
    //         content: {
    //             validators: {
    //                 callback: {
    //                     message: 'The content is required.',
    //                     callback: function(value, validator) {
    //                         var code = $('[name="content"]').code();
    //                         // <p><br></p> is code generated by Summernote for empty content
    //                         return (code !== '' && code !== '<p><br></p>');
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //   })
    //   .on('success.field.bv', function(e, data) {
    //     var $parent = data.element.parents('.form-group');

    //     // Remove the has-success class
    //     $parent.removeClass('has-success');

    //     // Hide the success icon
    //     $parent.find('.form-control-feedback[data-bv-icon-for="' + data.field + '"]').hide();
    //   })
    //   .find('[name="content"]').summernote({
    //       tabsize: 2,
    //       height: 150,
    //       onkeyup: function() {
    //           validateEditor();
    //       },
    //       onpaste: function() {
    //           validateEditor();
    //       }
    //   });

    //   function validateEditor() {
    //       $('#page').bootstrapValidator('revalidateField', 'content');
    //   };
    // });
</script>
@endpush