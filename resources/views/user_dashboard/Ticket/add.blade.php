@extends('user_dashboard.layouts.app')

@section('content')
    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-7 col-xs-12 mb20 marginTopPlus">
                    <div class="right mb10">
                        <!-- 					   <a href="#" class="btn btn-cust ticket-btn"><i class="fa fa-ticket"></i>&nbsp; New Ticket</a> -->
                    </div>
                    <div class="clearfix"></div>
                    @include('user_dashboard.layouts.common.alert')
                    <form action="{{url('ticket/store')}}" method="post" enctype="multipart/form-data" accept-charset="utf-8" id="ticket">
                        <div class="card">
                            <div class="card-header">
                                <h4>@lang('message.dashboard.ticket.add.title')</h4>
                            </div>
                            <div class="wap-wed mt20 mb20">
                                {{--<h3 class="ash-font">Create Ticket</h3>
                                <hr>--}}
                                <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">

                                <div class="form-group">
                                    <label for="subject">@lang('message.dashboard.ticket.add.name')<span class="text-danger">*</span></label>
                                    <input class="form-control" name="subject" id="subject" type="text"
                                           value="{{old('subject')}}">
                                    @if($errors->has('subject'))
                                        <span class="help-block">
									<strong class="text-danger">{{ $errors->first('subject') }}</strong>
								</span>
                                    @endif
                                </div>


                                <div class="form-group">
                                    <label for="description">@lang('message.dashboard.ticket.add.message')<span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control"
                                              id="description">{{old('description')}}</textarea>
                                    @if($errors->has('description'))
                                        <span class="help-block">
											<strong class="text-danger">{{ $errors->first('description') }}</strong>
										</span>
                                    @endif
                                    <p id="description-error" class="text-danger"></p>
                                </div>

                                <div class="form-group">
                                    <label>@lang('message.dashboard.ticket.add.priority')</label>
                                    <select class="form-control" name="priority" id="priority">
                                        <option value="Low">Low</option>
                                        <option value="Normal">Normal</option>
                                        <option value="High">High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-cust col-12" id="ticket_create">
                                    <i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="ticket_create_text">@lang('message.dashboard.button.submit')</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>
@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script>

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
})

$('#ticket').validate({
    rules: {
        subject: {
            required: true
        },
        description: {
            required: true
        }
    },
    submitHandler: function(form)
    {
        $("#ticket_create").attr("disabled", true);
        $(".spinner").show();
        $("#ticket_create_text").text('Submitting...');
        form.submit();
    }
});

// $(document).ready(function () {
//     $("#ticket").validate({
//         ignore: [],
//         rules: {
//             subject: {
//                 required: true
//             },
//             description: {
//                 ckeditor_required: true
//             }
//         }, messages: {
//             subject: {
//                 required: "This field is required"
//             }

//         },
//         errorPlacement:function(error,element){
//             if (element.attr("name") == "description" ) {
//                 $('#description-error').append(error);
//             }else{
//                 $( "label[for='" + element.attr( "id" ) + "']" ).append( error );
//             }
//         }
//     });

//     jQuery.validator.addMethod("ckeditor_required", function (value, element) {
//         var editorId = $(element).attr('id');
//         var messageLength = CKEDITOR.instances[editorId].getData().replace(/<[^>]*>/gi, '').length;
//         return messageLength;
//     }, "This field is required");

//     CKEDITOR.instances.description.on('change', function () {
//         if (CKEDITOR.instances.description.getData().length > 0) {
//             $('label[for="description"]').hide();
//         }
//     });
// });
</script>

@endsection