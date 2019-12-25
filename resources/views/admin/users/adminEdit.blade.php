@extends('admin.layouts.master')

@section('title', 'Edit Admin')

@section('page_content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border text-center">
                    <h3 class="box-title">Edit Admin</h3>
                </div>
                <form action="{{ url('admin/admin-users/update') }}" class="form-horizontal" id="user_form"
                      method="POST">

                    <input type="hidden" value="{{csrf_token()}}" name="_token" id="token">
                    <input type="hidden" value="{{$admin->id}}" name="admin_id" id="admin_id">

                    <div class="box-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                First Name
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" placeholder="Enter First Name" name="first_name" type="text"
                                       id="first_name" value="{{$admin->first_name}}">
                                </input>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                Last Name
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" placeholder="Enter Last Name" name="last_name" type="text"
                                       id="last_name" value="{{$admin->last_name}}">
                                </input>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label require">
                                Email
                            </label>
                            <div class="col-sm-6">
                                <input class="form-control" value="{{$admin->email}}" placeholder="Enter a valid email" name="email" type="email"
                                       id="email">
                                </input>
                                <span id="email_error"></span>
                                <span id="email_ok" class="text-success"></span>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label require">Group</label>
                            <div class="col-sm-6">
                                <select class="select2" name="role" id="role">
                                    @foreach ($roles as $role)
                                        <option <?= ($role->id==$admin->role_id) ? "selected":"" ?> value='{{ $role->id }}'> {{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- box-footer -->
                        <div class="box-footer">
                            <a class="btn btn-danger btn-flat pull-left" href="{{ url('admin/admin_users') }}"
                               id="users_cancel">Cancel</a>
                            <button type="submit" class="btn btn-primary pull-right btn-flat" id="users_create"><i
                                        class="spinner fa fa-spinner fa-spin" style="display: none;"></i> <span
                                        id="users_create_text">Submit</span></button>
                        </div>
                        <!-- /.box-footer -->
                    </div>
                    </input>
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
        $(".select2").select2({});
    })

    $.validator.setDefaults({
        highlight: function (element) {
            $(element).parent('div').addClass('has-error');
        },
        unhighlight: function (element) {
            $(element).parent('div').removeClass('has-error');
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element);
        }
    });

    // jQuery.validator.addMethod("letters_with_spaces_and_dot", function (value, element) {
    //     return this.optional(element) || /^[A-Za-z. ]+$/i.test(value); //letters + dot(.) symbol
    // }, "Please enter letters & only dot(.) symbol is allowed!");

    // jQuery.validator.addMethod("letters_with_spaces", function (value, element) {
    //     return this.optional(element) || /^[A-Za-z ]+$/i.test(value); //only letters
    // }, "Please enter letters only!");

    $('#user_form').validate({
        rules: {
            first_name: {
                required: true,
                // letters_with_spaces_and_dot: true
            },
            last_name: {
                required: true,
                // letters_with_spaces: true
            },
            email: {
                required: true,
                email: true
            }
        },
        submitHandler: function (form) {
            $("#users_create").attr("disabled", true);
            $(".spinner").show();
            $("#users_create_text").text('Creating...');
            $('#users_cancel').attr("disabled", "disabled");
            form.submit();
        }
    });

    // Validate Emal via Ajax
    $(document).ready(function ()
    {
        $("#email").on('keyup keypress', function (e)
        {
            if (e.type == "keyup" || e.type == "keypress")
            {
                var email = $('#email').val();
                var admin_id = $('#admin_id').val();

                if (email && admin_id)
                {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        method: "POST",
                        url: SITE_URL + "/admin/email_check",
                        dataType: "json",
                        data: {
                            'email': email,
                            'admin_id': admin_id,
                            'type': 'admin-email'
                        }
                    })
                    .done(function (response) {
                        // console.log(response);
                        if (response.status == true) {
                            emptyEmail();
                            if (validateEmail(email)) {
                                $('#email_error').addClass('error').html(response.fail).css("font-weight", "bold");
                                $('#email_ok').html('');
                                $('form').find("button[type='submit']").prop('disabled',true);
                            } else {
                                $('#email_error').html('');
                            }
                        }
                        else if (response.status == false) {
                            $('form').find("button[type='submit']").prop('disabled',false);
                            emptyEmail();
                            if (validateEmail(email)) {
                                $('#email_error').html('');
                            } else {
                                $('#email_ok').html('');
                            }
                        }

                        /**
                         * [validateEmail description]
                         * @param  {null} email [regular expression for email pattern]
                         * @return {null}
                         */
                        function validateEmail(email) {
                            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                            return re.test(email);
                        }

                        /**
                         * [checks whether email value is empty or not]
                         * @return {void}
                         */
                        function emptyEmail() {
                            if (email.length === 0) {
                                $('#email_error').html('');
                                $('#email_ok').html('');
                            }
                        }
                    });
                }
            }
        });
    });


</script>
@endpush


