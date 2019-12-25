@extends('admin.layouts.master')

@section('title', 'Edit Ticket')

@section('head_style')
    <!-- wysihtml5 -->
    <link rel="stylesheet" type="text/css" href="{{  asset('public/backend/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

    <!-- jquery-ui-1.12.1 -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/backend/jquery-ui-1.12.1/jquery-ui.min.css')}}">
@endsection

@section('page_content')

<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="top-bar-title padding-bottom">Edit Ticket</div>
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-body">

        <form class="form-horizontal" action="{{ url('admin/tickets/update') }}" method="POST" id="edit_ticket_form">
            {{csrf_field()}}

            <input type="hidden" name="code" value="{{ $ticket->code }}">

            <input type="hidden" name="id" value="{{ $ticket->id }}">

            <input id="user_id" type="hidden" name="user_id" value="{{ $ticket->user_id }}">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="col-sm-2 control-label require">Subject</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="subject" value="{{ $ticket->subject }}" id="subject">
                            @if($errors->has('subject'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label require">Message</label>
                        <div class="col-sm-10">
                            <textarea class="message form-control" name="message" id="message" cols="30" rows="10">{!!strip_tags($ticket->message)!!}</textarea>
                            @if($errors->has('message'))
                                <span class="help-block">
                                  <strong class="text-danger">{{ $errors->first('message') }}</strong>
                                </span>
                            @endif
                            <div id="error-message"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label require">Assignee</label>
                        <div class="col-sm-8">
                            <select name="assignee" class="form-control select2">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $admin->id == $ticket->admin_id ? 'selected' : '' }} >{{ $admin->first_name.' '.$admin->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Status</label>
                        <div class="col-sm-8">
                            <select name="status" class="form-control select2">
                                @foreach($ticket_statuses as $ticket_status)
                                    <option value="{{ $ticket_status->id }}" {{ $ticket_status->id == $ticket->ticket_status_id ? 'selected' : '' }}>{{ $ticket_status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label require">User</label>
                        <div class="col-sm-8">

                            <input id="user_input" type="text" name="user" placeholder="Enter Name" class="form-control" value="{{ $ticket->user->first_name.' '.$ticket->user->last_name }}"
                            {{  isset($user) && ($user->id == $ticket->user_id) ? 'selected' : '' }}>

                            <span id="error-user"></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="col-sm-4 control-label require">Priority</label>
                        <div class="col-sm-8">
                            <select name="priority" id="priority" class="form-control select2">
                                <option value="Low" {{ $ticket->priority == 'Low' ? 'selected' : '' }} >Low</option>
                                <option value="Normal" {{ $ticket->priority == 'Normal' ? 'selected' : '' }} >Normal</option>
                                <option value="High" {{ $ticket->priority == 'High' ? 'selected' : '' }} >High</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" id="assigned_email_div">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Email</label>
                        <div class="col-sm-8">
                            <input id="assigned_email" type="text" class="form-control" readonly name="email" value="{{ $ticket->user->email }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <a class="btn btn-danger btn-flat pull-left" href="{{ url('admin/tickets/list') }}">Cancel</a>
                    <button type="submit" class="btn btn-primary pull-right btn-flat" id="update_ticket"><i class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span id="update_ticket_text">Update</span></button>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('extra_body_scripts')

<!-- jquery.validate -->
<script src="{{ asset('public/dist/js/jquery.validate.min.js') }}" type="text/javascript"></script>

<!-- wysihtml5 -->
<script src="{{ asset('public/backend/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js') }}" type="text/javascript"></script>

<!-- jquery-ui-1.12.1 -->
<script src="{{ asset('public/backend/jquery-ui-1.12.1/jquery-ui.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
        $('.message').wysihtml5({
            events: {
                change: function () {
                    if($('#message').val().length === 0 )
                    {
                        $('#error-message').addClass('error').html('This field is required.').css("font-weight", "bold");
                    }
                    else
                    {
                        $('#error-message').html('');
                    }
                }
            }
        });
        $(".select2").select2({});
    });

    $.validator.setDefaults({
        highlight: function(element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element)
        {
            if (element.prop('name') === 'message')
            {
                $('#error-message').html(error);
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('#edit_ticket_form').validate({
        ignore: ":hidden:not(textarea)",
        rules: {
            subject: {
                required: true,
            },
            message: "required",
            user: {
                required: true,
            },
        },
        submitHandler: function(form)
        {
            $("#update_ticket").attr("disabled", true);
            $(".spinner").show();
            $("#update_ticket_text").text('Updating...');
            $('#update_ticket').click(false);
            form.submit();
        }
    });

    $(document).ready(function()
    {
        $("#user_input").on('keyup keypress', function(e)
        {
            if (e.type=="keyup" || e.type=="keypress")
            {
                $('#assigned_email_div').hide();

                var user_input = $('form').find("input[name='user']").val();

                if(user_input.length === 0)
                {
                    $('#user_id').val('');
                    $('#error-user').html('');
                }
            }
        });

        $('#user_input').autocomplete(
        {
            source:function(req,res)
            {
                if (req.term.length > 0)
                {
                    $.ajax({
                        url:'{{ url('admin/ticket_user_search') }}',
                        dataType:'json',
                        type:'get',
                        data:{
                            search:req.term
                        },
                        success:function (response)
                        {
                            // console.log(response);

                            $('form').find("button[type='submit']").prop('disabled',true);

                            if(response.status == 'success')
                            {
                                res($.map(response.data, function (item)
                                {
                                    $('#assigned_email_div').show();

                                    return {
                                            id : item.user_id,
                                            first_name : item.first_name,
                                            last_name : item.last_name,
                                            value: item.first_name + ' ' + item.last_name, //don't change value property name
                                            email: item.email,
                                        }
                                    }
                                ));

                            }
                            else if(response.status == 'fail')
                            {
                                $('#assigned_email').val('');
                                $('#assigned_email_div').hide();
                                $('#error-user').addClass('text-danger').html('User Does Not Exist!');
                            }
                        }
                    })
                }
                else
                {
                    // console.log(req.term.length);
                    $('#user_id').val('');
                }
            },
            select: function (event, ui)
            {
                var e = ui.item;

                $('form').find("button[type='submit']").prop('disabled',false);

                $('#error-user').html('');

                $('#user_id').val(e.id);

                $('#assigned_email').val(e.email);
            },
            minLength: 0,
            autoFocus: true
        });
    });

</script>

@endpush

