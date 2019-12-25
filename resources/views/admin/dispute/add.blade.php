@extends('admin.layouts.master')
@section('title', 'Create Dispute')

@section('page_content')

<div class="row">
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border text-center">
              <h3 class="box-title">Add Dispute</h3>
            </div>

            <form method="POST" action="{{url('admin/dispute/open')}}" class="form-horizontal" id="dispute_add_form" accept-charset='UTF-8'>

                <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                <input type="hidden" name="transaction_id" value="{{$transaction->id}}">
                <input type="hidden" name="claimant_id" value="{{$transaction->user_id}}">
                <input type="hidden" name="defendant_id" value="{{$transaction->end_user_id}}">

                <div class="box-body">
                  <div class="form-group">
                    <label class="col-sm-3 control-label" for="title">Title</label>
                    <div class="col-sm-6">
                      <input type="text" class="form-control" value="{{old('title')}}" name="title" id="title">
                      <span class="text-danger">{{ $errors->first('title') }}</span>
                    </div>
                  </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="reason_id">Reason</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="reason_id" id="reason_id">
                                @foreach ($reasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="description">Description</label>
                        <div class="col-sm-6">
                          <textarea class="form-control" rows="5" name="description" id="description">{{old('description')}}</textarea>
                          <span class="text-danger">{{ $errors->first('description') }}</span>
                        </div>
                    </div>

                    <div class="box-footer">
                        <a href="{{ url("admin/disputes") }}" class="btn btn-danger btn-flat">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-flat pull-right" id="submit">
                            <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="dispute_add_text">Submit</span>
                        </button>
                    </div>
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

$('#dispute_add_form').validate({
    rules: {
        title: {
            required: true,
        },
        description: {
            required: true,
        },
    },
    submitHandler: function(form)
    {
        $("#submit").attr("disabled", true);
        $(".spinner").show();
        $("#dispute_add_text").text('Submit...');
        form.submit();
    }
});

</script>
@endpush