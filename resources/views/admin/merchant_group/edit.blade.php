@extends('admin.layouts.master')

@section('title', 'Edit Merchant Group')

@section('page_content')
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Edit Merchant Package</h3>
                </div>

                <!-- form start -->
                <form method="POST" action="{{ url('admin/settings/edit-merchant-group/'. $merchantGroup->id) }}" class="form-horizontal" id="merchant-group-edit-form">
                    {{ csrf_field() }}

                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" name="name" class="form-control" value="{{ $merchantGroup->name }}" placeholder="Edit name" id="name">
                                @if($errors->has('name'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Description</label>
                            <div class="col-sm-6">
                                <textarea placeholder="Edit description" rows="3" class="form-control" name="description" id="description">{{ $merchantGroup->description }}</textarea>
                                @if($errors->has('description'))
                                    <span class="help-block">
                                    <strong class="text-danger">{{ $errors->first('description') }}</strong>
                                  </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Fee (%)</label>
                            <div class="col-sm-6">
                                <input type="text" name="fee" class="form-control" value="{{ $merchantGroup->fee }}" placeholder="Edit fee" id="fee">
                                @if($errors->has('fee'))
                                    <span class="help-block">
                                        <strong class="text-danger">{{ $errors->first('fee') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group">
                          <label class="col-sm-3 control-label" for="exampleFormControlInput1">Default</label>
                          <div class="col-sm-6">

                            @if ($merchantGroup->is_default == 'Yes')
                                  <p class="form-control-static"><span class="label label-success">{{$merchantGroup->is_default}}</span></p>
                                  <input type="hidden" value="{{ $merchantGroup->is_default }}" name="default">
                            @else
                                <select class="select2" name="default" id="default">
                                    <option value='No' {{ $merchantGroup->is_default == 'No' ? 'selected':"" }}>No</option>
                                    <option value='Yes' {{ $merchantGroup->is_default == 'Yes' ? 'selected':"" }}>Yes</option>
                                </select>
                            @endif

                          </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <a class="btn btn-danger" href="{{ url('admin/settings/merchant-group') }}">Cancel</a>
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

    $(function () {
      $(".select2").select2({
      });
    });

    jQuery.validator.addMethod("letters_with_spaces", function (value, element) {
        return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
    }, "Please enter letters only!");

    $.validator.setDefaults({
        highlight: function (element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function (element) {
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


    $('#merchant-group-edit-form').validate({
        rules: {
            name: {
                required: true,
                letters_with_spaces: true,
            },
            description: {
                required: true,
            },
            fee: {
                required: true,
                number: true,
            },
        },
    });
</script>

@endpush
