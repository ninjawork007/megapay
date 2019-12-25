@extends('admin.layouts.master')

@section('title', 'Edit Meta')

@section('page_content')

  <div class="row">
      <div class="col-md-3 settings_bar_gap">
        @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
        <!-- Horizontal Form -->
        <div class="box box-info">
          <div class="box-header with-border text-center">
            <h3 class="box-title">Edit Meta</h3>
          </div>

          <!-- form start -->
          <form method="POST" action="{{ url('admin/settings/edit_meta/'.$result->id) }}" class="form-horizontal" id="meta_edit_form">
              {{ csrf_field() }}

              <div class="box-body">

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Page Url</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control" name="url" value="{{ $result->url }}" placeholder="url" id="url">
                      @if($errors->has('url'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('url') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Page Title</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control" name="title" value="{{ $result->title }}" placeholder="title" id="title">
                      @if($errors->has('title'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('title') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Meta Description</label>
                    <div class="col-sm-6">
                      <textarea rows="3" class="form-control" name="description" placeholder="meta description" id="description">{{ $result->description }}</textarea>
                      @if($errors->has('description'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('description') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="col-sm-3 control-label">Keywords</label>
                    <div class="col-sm-6">
                      <textarea rows="3" class="form-control" name="keywords" placeholder="meta keywords" id="keywords">{{ $result->keywords }}</textarea>
                      @if($errors->has('keywords'))
                        <span class="help-block">
                          <strong class="text-danger">{{ $errors->first('keywords') }}</strong>
                        </span>
                      @endif
                    </div>
                  </div>

              </div>

              <div class="box-footer">
                <a class="btn btn-danger" href="{{ url('admin/settings/metas') }}">Cancel<a>
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

  $('#meta_edit_form').validate({
    rules: {
      url: {
        required: true,
      },
      title: {
        required: true,
        // letters_with_spaces: true,
      },
      description: {
        required: true,
        // letters_with_spaces: true,
      },
      keywords: {
        // required: true,
        // letters_with_spaces: true,
      },
    },
  });
</script>

@endpush
