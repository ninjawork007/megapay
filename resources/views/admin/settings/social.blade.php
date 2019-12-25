@extends('admin.layouts.master')
@section('title', 'Social Settings')

@section('page_content')

    <!-- Main content -->
    <div class="row">
        <div class="col-md-3 settings_bar_gap">
            @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Social Links Form</h3>
                </div>

                <form action="{{ url('admin/settings/social_links') }}" method="post" class="form-horizontal"
                      id="social_links">
                {!! csrf_field() !!}

                <!-- box-body -->
                    <div class="box-body">
                    @foreach($result as $row)
                        <!-- facebook -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label">{{str_replace('_',' ',ucfirst($row->name))}}</label>
                                <div class="col-sm-6">
                                    <input type="text" name="{{$row->name}}" class="form-control"
                                           value="{{ $row->url }}">

                                    @if($errors->has($row->name))
                                        <span class="help-block">
		                          <strong class="text-danger">{{ $errors->first($row->name) }}</strong>
		                        </span>
                                    @endif
                                </div>
                            </div>
                    @endforeach

                    </div>
                    <!-- /.box-body -->

                    <!-- box-footer -->
                    @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_social_links'))
                        <div class="box-footer">
                            <button class="btn btn-primary btn-flat pull-right" type="submit">Submit</button>
                        </div>
                    @endif
                    <!-- /.box-footer -->
                </form>
            </div>
        </div>
    </div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">

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

    $('#social_links').validate({
        rules: {
            facebook: {
                // required: true,
                url: true,
            },
            google_plus: {
                // required: true,
                url: true,
            },
            twitter: {
                // required: true,
                url: true,
            },
            linkedin: {
                // required: true,
                url: true,
            },
            pinterest: {
                // required: true,
                url: true,
            },
            youtube: {
                // required: true,
                url: true,
            },
            instagram: {
                // required: true,
                url: true,
            },
        },
    });

</script>

@endpush
