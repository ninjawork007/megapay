@extends('user_dashboard.layouts.app')

@section('content')

    <section class="section-06 history padding-30">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-xs-12 col-sm-12 mb20 marginTopPlus">
                    <div class="flash-container">
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>@lang('message.dashboard.ticket.details.sidebar.header')</h4>
                        </div>
                        <div>

                            <div class="ticket-line mt10">
                                <div class="titlecolor-txt">@lang('message.dashboard.ticket.details.sidebar.ticket-id')</div>
                                <div class="generalcolor-txt">{{ $ticket->code }}</div>
                            </div>
                            <hr/>

                            <div class="ticket-line mt10">
                                <div class="titlecolor-txt">@lang('message.dashboard.ticket.details.sidebar.subject')</div>
                                <div class="generalcolor-txt">{{ $ticket->subject }}</div>
                            </div>
                            <hr/>

                            <div class="ticket-line">
                                <div class="titlecolor-txt">@lang('message.dashboard.ticket.details.sidebar.date')</div>
                                <div class="generalcolor-txt">{{ dateFormat($ticket->created_at) }}</div>
                            </div>
                            <hr/>

                            <div class="ticket-line mb20">
                                <div class="titlecolor-txt">@lang('message.dashboard.ticket.details.sidebar.priority')</div>
                                <div class="generalcolor-txt">{{ $ticket->priority }}</div>
                            </div>

                            <hr>
                            <div class="ticket-line mb20">
                                <div class="titlecolor-txt">@lang('message.dashboard.ticket.details.sidebar.status')</div>
                                <div class="generalcolor-txt">
                                    @if($ticket->ticket_status->name =='Closed')
                                        <span class="badge badge-danger">{{ $ticket->ticket_status->name }}</span>
                                    @elseif($ticket->ticket_status->name =='Hold')
                                        <span class="badge badge-warning">{{ $ticket->ticket_status->name }}</span>
                                    @elseif($ticket->ticket_status->name =='In Progress')
                                        <span class="badge badge-primary">{{ $ticket->ticket_status->name }}</span>
                                    @elseif($ticket->ticket_status->name =='Open')
                                        <span class="badge badge-success">{{ $ticket->ticket_status->name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="ticket-btn ticket-line mb20 d-none">
                                <select class="form-control" name="status" id="status">
                                    @foreach($ticket_status as $val)
                                        <option value="{{$val->id}}" <?= ($ticket->ticket_status->id == $val->id) ? 'selected' : ''  ?> >{{$val->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="ticket_id" value="{{ $ticket->id }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 col-xs-12 col-sm-12 mb20 marginTopPlus">

                    @include('user_dashboard.layouts.common.alert')
                    <span id="alertDiv">
						</span>

                    <div class="flash-container">
                        <h2 class="ash-font">@lang('message.dashboard.ticket.details.form.title')</h2>
                    </div>
                    <hr>
                    @if($ticket->ticket_status->name != 'Closed')
                        <form action="{{url('ticket/reply_store')}}" id="reply" method="post"
                              enctype="multipart/form-data">
                            <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                            {{ csrf_field() }}
                            <div class="mt20 mb20">
                                <div class="h6">@lang('message.dashboard.ticket.details.form.message')
                                    <spam class="text-danger">*</spam>
                                </div>

                                <textarea name="description" id="description" class="form-control"></textarea>
                                @if($errors->has('description'))
                                    <span class="error">
    								{{ $errors->first('description') }}
    							</span>
                                @endif
                                <p id="description-error" class="text-danger"></p>
                            </div>
                            <div class="file-box">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label" for="exampleInputFile">@lang('message.dashboard.ticket.details.form.file')</label>
                                            <input class="form-control" type="file" name="file" id="file">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group d-none">
                                            <label class="control-label" for="exampleInputFile">@lang('message.dashboard.ticket.details.sidebar.status')</label>
                                            <select class="form-control" name="status_id" id="status_id">
                                                @foreach($ticket_status as $val)
                                                    <option value="{{$val->id}}">{{$val->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="text-right">
                                            <br><br>
                                            <button class="btn btn-cust">@lang('message.dashboard.button.submit')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif

                    <br>
                    <div class="">
                        <div class="reply-views">
                            <div class="reply-box">
                                <div class="left">

                                    <div class="profile-id-pic left">
                                        @if(!empty($ticket->user->picture))
                                            <?php
                                            $userTicketAvatar = $ticket->user->picture;
                                            ?>
                                            <img src='{{url("public/user_dashboard/profile/$userTicketAvatar")}}'
                                                 class="rounded-circle" style="width:60px;">
                                        @else
                                            <img src="{{url('public/user_dashboard/images/avatar.jpg')}}" alt=""
                                                 class="rounded-circle" style="width:60px;">
                                        @endif
                                    </div>

                                    <div class="left">
                                        <h5 class="">{{$ticket->user->first_name.' '.$ticket->user->last_name}}</h5>
                                    </div>
                                </div>
                                <div class="right">
                                    <div class="update-time">{{ dateFormat($ticket->created_at) }}</div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="reply-details">
                                <p>{!! $ticket->message !!}</p>
                            </div>
                        </div>
                    </div>
                    <br>

                    @if( $ticket_replies->count() > 0 )
                        @foreach($ticket_replies as $result)
                            @if($result->user_type == 'user' )
                                <div class="">
                                    <div class="reply-views">
                                        <div class="reply-box">
                                            <div class="left">

                                                <div class="profile-id-pic left">
                                                    @if(!empty($result->user->picture))
                                                        <?php
                                                        $userAvatar = $result->user->picture;
                                                        ?>
                                                        <img src='{{url("public/user_dashboard/profile/$userAvatar")}}'
                                                             class="rounded-circle" style="width:60px;">
                                                    @else
                                                        <img src="{{url('public/user_dashboard/images/avatar.jpg')}}"
                                                             alt="" class="rounded-circle" style="width:60px;">
                                                    @endif
                                                </div>

                                                <div class="left">
                                                    <h5 class="">{{$result->user->first_name.' '.$result->user->last_name}}</h5>
                                                    <!-- <p class="mt6 ash-font">Staff</p> -->
                                                </div>

                                            </div>
                                            <div class="right">
                                                {{-- <div class="update-time">{{date('d-m-Y h:i A', strtotime($result->created_at))}}</div> --}}
                                                <div class="update-time">{{ dateFormat($result->created_at) }}</div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="reply-details">

                                            <p>{!! $result->message !!}</p>
                                            @if($result->file)
                                                ----------------<br>
                                                <h5>
                                                    <a class="text-info"
                                                       href="{{url('public/uploads/ticketFile').'/'.$result->file->filename}}">
                                                        <i class="fa fa-download"></i> {{$result->file->originalname}}
                                                    </a>
                                                </h5>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <br>
                            @else

                                <div class="">
                                    <div class="reply-views">
                                        <div class="reply-box">
                                            <div class="left">
                                                <div class="profile-id-pic left">

                                                    @if(!empty($result->admin->picture))
                                                        <?php
                                                        $adminAvatar = $result->admin->picture;
                                                        ?>
                                                        <img src='{{url("public/uploads/userPic/$adminAvatar")}}'
                                                             class="rounded-circle" style="width:60px;">
                                                    @else
                                                        <img src="{{url('public/user_dashboard/images/avatar.jpg')}}"
                                                             alt="" class="rounded-circle" style="width:60px;">
                                                    @endif

                                                </div>
                                                <div class="left">
                                                    <h5 class="">{{ $result->admin->first_name.' '.$result->admin->last_name }}</h5>
                                                    <!-- <p class="mt6 ash-font">Staff</p> -->
                                                </div>
                                            </div>
                                            <div class="right">
                                                {{-- <div class="update-time">{{date('d-m-Y h:i A', strtotime($result->created_at))}}</div> --}}
                                                <div class="update-time">{{ dateFormat($result->created_at) }}</div>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="reply-details">

                                            <p>{!! $result->message !!}</p>
                                            @if($result->file)
                                                ----------------<br>
                                                <h5>
                                                    <a class="text-info"
                                                       href="{{url('public/uploads/ticketFile').'/'.$result->file->filename}}">
                                                        <i class="fa fa-download"></i> {{$result->file->originalname}}
                                                    </a>
                                                </h5>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <br>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!--End Section-->
@endsection

@section('js')

<script src="{{asset('public/user_dashboard/js/jquery.validate.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/additional-methods.min.js')}}" type="text/javascript"></script>

<script>

jQuery.extend(jQuery.validator.messages, {
    required: "{{__('This field is required.')}}",
})

$('#reply').validate({
    rules: {
        description: {
            required: true,
        },
        file: {
            extension: "docx|rtf|doc|pdf|png|jpg|jpeg|gif|bmp",
        },
    },
    messages: {
      file: {
        extension: "{{__("Please select (docx, rtf, doc, pdf, png, jpg, jpeg, gif or bmp) file!")}}"
      },
    },
});

$("#status").on('change', function () {
    var status_id = $(this).val();
    var ticket_id = $("#ticket_id").val();

    $.ajax({
        method: "POST",
        url: SITE_URL + "/ticket/change_reply_status",
        data: {status_id: status_id, ticket_id: ticket_id}
    })
    .done(function (reply) {
        message = 'Ticket reply status ' + reply.status + ' successfully done.';
        var messageBox = '<div class="alert alert-success" role="alert">' + message + '</div><br>';
        $("#alertDiv").html(messageBox);
        setTimeout(function () {
            location.reload()
        }, 2000);
    });
});

// $(document).ready(function () {

//     $("#reply").validate({
//         ignore: [],

//         rules: {
//             description: {
//                 ckeditor_required: true
//             }
//         },
//         errorPlacement: function (error, element) {
//             if (element.attr("name") == "description") {
//                 $('#description-error').append(error);
//             } else {
//                 $("label[for='" + element.attr("id") + "']").append(error);
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