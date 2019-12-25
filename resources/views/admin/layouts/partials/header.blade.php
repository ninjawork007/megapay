<header class="main-header">
    <!-- Logo -->
    <div class="full-width">

    <a href="{{ route('dashboard') }}" class="logo">
        <span class="logo-mini"><b>{{$app_name_short}}</b></span>

        @if (!empty($company_logo))
        <img src="{{ url('public/images/logos/'.$company_logo) }}" width="180" height="50">
        @else
            <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" width="180">
        @endif
    </a>
    </div>

    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="mobile-width">
            <a href="{{ route('dashboard') }}" class="mobile-logo">
                <span class="logo-lg" style="font-size: 13px;"><b>{{$app_name_long}}</b></span>
            </a>
        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                {{-- @include('admin.layouts.partials.nav_language') --}}
                @include('admin.layouts.partials.nav_user-menu')
            </ul>
        </div>
    </nav>
</header>

<!-- Flash Message  -->
<div class="flash-container">
    @if(Session::has('message'))
        <div class="alert {{ Session::get('alert-class') }} text-center" style="margin-bottom:0px;" role="alert">
          {{ Session::get('message') }}
          <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
        </div>
    @endif
    <div class="alert alert-success text-center" id="success_message_div" style="margin-bottom:0px;display:none;" role="alert">
        <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
        <p id="success_message"></p>
    </div>

    <div class="alert alert-danger text-center" id="error_message_div" style="margin-bottom:0px;display:none;" role="alert">
        <p><a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a></p>
        <p id="error_message"></p>
    </div>
</div>
<!-- /.Flash Message  -->


