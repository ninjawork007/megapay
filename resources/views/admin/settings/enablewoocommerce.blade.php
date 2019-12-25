@extends('admin.layouts.master')
@section('title', 'Preferences')

@section('page_content')
  <!-- Main content -->
  <div class="row">
      <div class="col-md-3 settings_bar_gap">
          {{-- settings_bar --}}
          @include('admin.common.settings_bar')
      </div>
      <div class="col-md-9">
          <div class="box box-info">
              <div class="box-header with-border text-center">
                <h3 class="box-title">Enable WooCommerce</h3>
              </div>
              <form action="{{ url('admin/settings/enable-woocommerce') }}" method="post" id="myform1" class="form-horizontal" enctype="multipart/form-data">
                {!! csrf_field() !!}

                  <div class="box-body">

                    @if($code_status != 1)
                      <input type="hidden" name="key" value="purchasecodeverification">
                      <div class="form-group">
                          <label class="col-sm-3 control-label" for="inputEmail3">Purchase Code</label>
                          <div class="col-sm-6">
                              <input type="text" class="form-control" placeholder="Enter Purchase Code" value="{{old('envatopurchasecode')?old('envatopurchasecode'):''}}" name="envatopurchasecode"/>
                              <span class="text-danger">{{$errors->first('envatopurchasecode')}}</span>
                          </div>
                      </div>
                    @else

                      <input type="hidden" name="pluginUploaded" id="pluginUploaded" value="{{ !empty($plugin_name) ? $plugin_name : null }}">

                      <!-- Plugin file upload -->
                      <div class="form-group">
                          <label class="col-sm-3 control-label" for="Plugin">Upload plugin zip file</label>
                          <div class="col-sm-6">
                            <input type="file" name="plugin" id="plugin" class="form-control input-file-field" value="{{ old('plugin') }}">
                            <span class="file-validation-error"></span>
                            <!-- -->
                            @if (!empty($plugin_name))
                                <h5>
                                    <a class="text-info" href="{{ url('public/uploads/woocommerce').'/'.$plugin_name }}">
                                      <i class="fa fa-download"></i>
                                        {{ $plugin_name }}
                                    </a>
                                </h5>
                            @endif
                            <!-- -->
                            <span class="text-danger">{{ $errors->first('plugin') }}</span>
                          </div>
                      </div>
                        <!-- Status -->
                      <div class="form-group">
                          <label for="inputEmail3" class="col-sm-3 control-label">Publication status</label>
                          <div class="col-sm-6">
                            <select class="form-control" name="publication_status" id="publication_status">
                              <option value="" selected>Select status</option>
                                <option value="Active" {{ !empty($publicationStatus) && $publicationStatus == 'Active' ? 'selected':"" }}>Active</option>
                                <option value="Inactive" {{ !empty($publicationStatus) && $publicationStatus == 'Inactive' ? 'selected':"" }}>Inactive</option>
                            </select>
                            <span class="text-danger">{{ $errors->first('publication_status') }}</span>
                          </div>
                      </div>
                    @endif

                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_enable_woocommerce'))
                      <div class="row">
                          <div class="col-md-9">
                              <button class="btn btn-primary btn-flat pull-right" type="submit" id="woocommerce-submit">Submit</button>
                          </div>
                      </div>
                    @endif

                  </div>
              </form>
          </div>
      </div>
  </div>
  <!-- /.box -->
@endsection

@push('extra_body_scripts')

    <!-- jquery.validate -->
    <script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">

        $(window).on('load',function(){
            $(".select2").select2();
        })


        $( document ).ready(function()
        {
           //on file change
          $('#plugin').bind("change", function(e)
          {
              var file = (e.srcElement || e.target).files[0];
              console.log(file.type);

              $(this).attr("data-plugin-type", file.type);

              if (file.type != 'application/x-zip-compressed')
              {
                $('.file-validation-error').html("The plugin must be a zip file.")
                .css({
                    'color': 'red',
                    'font-size': '14px',
                    'font-weight': '800',
                    'padding-top': '5px',
                });
                $('#woocommerce-submit').attr("disabled", true);
              }
              else
              {
                  var fileSize = e.target.files[0].size / 1024 / 1024; // in MB
                  if (fileSize > 2)
                  {
                    $('.file-validation-error').html("The plugin file size must be less than 2 MB.")
                    .css({
                        'color': 'red',
                        'font-size': '14px',
                        'font-weight': '800',
                        'padding-top': '5px',
                    });
                    $('#woocommerce-submit').attr("disabled", true);
                  }
                  else
                  {
                    $('.file-validation-error').html('');
                    $('#woocommerce-submit').attr("disabled", false);
                  }
              }
          });
        });

        //on status change
        $(document).on('change', '#publication_status', function(event)
        {
            var pluginValue = $('#plugin').val();
            var pluginUploaded = $('#pluginUploaded').val();
            if ($(this).val() == 'Active')
            {
              if (pluginValue == '' && pluginUploaded == '')
              {
                  $('.file-validation-error').html("Please upload plugin first.")
                  .css({
                      'color': 'red',
                      'font-size': '14px',
                      'font-weight': '800',
                      'padding-top': '5px',
                  });
                  $('#woocommerce-submit').attr("disabled", true);
              }
              else
              {
                var pluginDataAttributeType = $('#plugin').attr("data-plugin-type");
                if (pluginDataAttributeType != 'application/x-zip-compressed' && pluginValue != '')
                {
                    $('.file-validation-error').html("The plugin must be a zip file.")
                    .css({
                      'color': 'red',
                      'font-size': '14px',
                      'font-weight': '800',
                      'padding-top': '5px',
                    });
                    $('#woocommerce-submit').attr("disabled", true);
                }
              }
            }
            else
            {
              // $('#plugin').val('');
              $('.file-validation-error').html('');
              $('#woocommerce-submit').attr("disabled", false);
            }
        });

        $('#myform1').validate({
          rules: {
              publication_status: {
                  required: true,
              },
          },
          // submitHandler: function (form) {
          //     $("#send_money").attr("disabled", true);
          //     $(".spinner").show();
          //     $("#send_text").text('Sending...');
          //     var pretxt=$("#send_text").text();
          //     form.submit();
          //     setTimeout(function(){
          //         $("#send_money").removeAttr("disabled");
          //         $(".spinner").hide();
          //         $("#send_text").text(pretxt);
          //     },2000);
          // }
        });
    </script>
@endpush