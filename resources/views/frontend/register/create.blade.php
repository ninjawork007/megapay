<div class="box box-default">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="top-bar-title padding-bottom">
                    Users
                </div>
            </div>
        </div>
    </div>
</div>
<div class="box">

    <!-- form start -->
    <form action="{{ url('register') }}" class="form-horizontal" id="users_form" method="POST">
        {{ csrf_field() }}
        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <input id="user_type" name="user_type" type="hidden" value="">
                            <h4 class="text-info text-center">
                                Users Information
                            </h4>
                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Username
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="username" type="text" value="">
                                    </input>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    First Name
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="first_name" type="text" value="">
                                    </input>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Last Name
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="last_name" type="text" value="">
                                    </input>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Phone
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="phone" type="text" value="">
                                    </input>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Email
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="email" type="email" value="">
                                    </input>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Password
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="password" type="password" value="" id="password">
                                    </input>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label require" for="inputEmail3">
                                    Confirm Password
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="password_confirmation" type="password" id="password_confirmation">
                                    </input>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-4 control-label" for="inputEmail3">
                                    Phrase
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control" name="phrase" type="text" value="">
                                    </input>
                                </div>
                            </div>
                        </input>
                    </div>
                </div>
            </div>
            <br>
            </br>
        </div>
        <!-- box-footer -->
        <div class="box-footer">
            <a class="btn btn-danger btn-flat" href="{{ url('/') }}">
                Cancel
            </a>
            <button class="btn btn-primary pull-right btn-flat" type="submit">
                Submit
            </button>
        </div>
        <!-- /.box-footer -->
    </form>
</div>
<script type="text/javascript">

  $('#password-form').validate({
        rules: {
            username: {
                required: true,
            },
            first_name: {
                required: true,
            },
            last_name: {
                required: true,
            },
            phone: {
                required: true,
            },
            email: {
                required: true,
            },
            password: {
                required: true,
                minlength: 5
            },
            password_confirmation: {
                required: true,
                minlength: 5,
                equalTo: "#password"
            }
        }
    });

</script>
