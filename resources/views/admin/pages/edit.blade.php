@extends('admin.layouts.master')
@section('title', 'Edit Page')

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
             <div class="top-bar-title padding-bottom">Edit Page</div>
            </div>
            <div class="col-md-2">
              @if(Common::has_permission(Auth::guard('admin')->user()->id, 'manage_page'))
                <div class="top-bar-title padding-bottom">
                <a href="{{ url("admin/settings/pages") }}" class="btn btn-block btn-default btn-flat btn-border-orange">Pages</a>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="box">
      <div class="box-body">
        <!-- /.box-header -->
        <form action="{{url('admin/settings/page/update')}}" method="post" id="page" class="form-horizontal" enctype="multipart/form-data">
           {{ csrf_field() }}

           <input class="form-control" name="id" value="{{ $page->id }}" type="hidden">

          <div class="box-body">
            <div class="form-group">
              <label class="col-sm-2 control-label required" for="inputEmail3">Name</label>
              <div class="col-sm-8">
                <input class="form-control" name="name" value="{{ $page->name }}" type="text">
                  @if ($errors->has('name'))
                      <span class="error">
                          <strong>{{ $errors->first('name') }}</strong>
                      </span>
                  @endif
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label required" for="inputEmail3">Content</label>
              <div class="col-sm-8">
                <textarea id="content" class="form-control" name="content" placeholder="Content" rows="10" cols="80" >{{ $page->content }}</textarea>
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
                            <input type="checkbox" name="header" <?= in_array('header',$page->position)?'checked="true"':'' ?> class="position" id="header">
                            Header
                        </label>
                        <label>
                            <input type="checkbox" name="footer" <?= in_array('footer',$page->position)?'checked="true"':'' ?> class="position" id="footer">
                            Footer
                        </label>
                    </div>
                    <div id="error-message"></div>
                </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label required" for="inputEmail3">Status</label>
              <div class="col-sm-8">
                <select class="select2" name="status">
                    <option value="active" <?= ( $page->status == 'active' ) ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ( $page->status == 'inactive' ) ? 'selected' : '' ?>>Inactive</option>
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
            <button class="btn btn-primary pull-right btn-flat" type="submit">Update</button>
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
        }
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
          $('#error-message').addClass('error').html('Please check at least one box.').css("font-weight", "bold");
          return false;
        }
    }
</script>
@endpush