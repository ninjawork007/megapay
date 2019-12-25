<?php
	$user = Auth::user();
	$socialList = getSocialLink();
	$menusHeader = getMenuContent('Header');
	$logo = session('company_logo');
	$company_name = getCompanyName();
	$socialList = getSocialLink();
	$menusFooter = getMenuContent('Footer');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <meta name="csrf-token" content="{{ csrf_token() }}"><!-- for ajax -->

        <meta name="description" content="{{!isset($exception) ? meta(Route::current()->uri(),'description'):$exception->description}}">
        <meta name="keywords" content="{{!isset($exception) ? meta(Route::current()->uri(),'keyword'):$exception->keyword}}">
        <title>{{!isset($exception) ? meta(Route::current()->uri(),'title'):$exception->title}} <?= isset($additionalTitle)?'| '.$additionalTitle :'' ?></title>

        <!--css styles-->
        <link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/bootstrap.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/themify-icons.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/font-awesome-4.7.0/css/font-awesome.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/reset.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/style.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/responsive.css')}}">
		<link rel="stylesheet" type="text/css" href="{{asset('public/user_dashboard/css/animate.min.css')}}">

		<!-- iCheck -->
		<link rel="stylesheet" type="text/css" href="{{ url('public/user_dashboard/css/iCheck/square/blue.css') }}">

        <!---title logo icon-->
        <link rel="javascript" type="text/css" href="{{asset('public/user_dashboard/js/respond.js')}}">
        <link rel="shortcut icon" type="text/css" href="{{asset('public/images/logos/'.getfavicon())}}" />

		<!-- must include below if using auth middleware, or, creates problem in ajax token -->
		<script>
	       window.Laravel = {!! json_encode([
	           'csrfToken' => csrf_token(),
	       ]) !!};
	  	</script>
	  	<!--/-->

        <script type="text/javascript">
        	var SITE_URL = "{{url('/')}}";
        </script>

        <style type="text/css">
            #image-dropdown {
              display: inline-block;
              border: 1px solid;
            }
            #image-dropdown {
              height: 30px;
              overflow: hidden;
            }
            /*#image-dropdown:hover {} */

            #image-dropdown .img_holder {
              cursor: pointer;
            }
            #image-dropdown img.flagimgs {
              height: 30px;
            }
            #image-dropdown span.iTEXT {
              position: relative;
              top: -8px;
            }
            .navbar.navbar-expand-lg.navbar-dark.bg-primary.toogleMenuDiv{
                padding:0 !important;
            }
        </style>
    </head>
    <body>

        <!-- Start Preloader -->
        <div class="preloader">
            <div class="preloader-img"></div>
        </div>
        <!-- End Preloader -->

		<header id="js-header-old">
		    <nav class="navbar navbar-expand-lg navbar-dark bg-primary toogleMenuDiv" style="max-height: 61px;">
		        <div class="container">
		            @if (isset($logo))
		                <a style="max-height:100%;margin-left:5px;width: 157px;overflow: hidden;" class="navbar-brand logo_area" href="{{url('/')}}">
		                    <img src="{{asset('public/images/logos/'.$logo)}}" alt="logo" class="img-responsive img-fluid">
		                </a>
		            @else
		                <a style="max-height:100%;margin-left:5px;width: 157px;overflow: hidden;" class="navbar-brand logo_area" href="{{url('/')}}">
		                    <img src="{{ url('public/uploads/userPic/default-logo.jpg') }}" class="img-responsive" width="80" height="50">
		                </a>
		            @endif
		        </div>
		    </nav>
		</header>

        <!--Start Section-->
		<section class="section-01 sign-up padding-30">
		    <div class="container">
		        <div class="row">
		            <div class="col-md-12">
		                <div class="row">
		                    <div class="col-md-8 mx-auto">

		                        <div class="card rounded-0">
		                            <div class="card-header">
		                                <h3 class="text-center">@lang('message.2sa.title-text')</h3>
		                            </div>
		                            <div style="padding: 30px;">

										<div class="alert text-center" id="message" style="display: none"></div>

		                                <form class="form-horizontal" method="POST" id="2sa_form"><!--submitting via ajax-->

											<input type="hidden" name="fingerprint" id="fingerprint" class="form-control">

		                                    <div class="form-group">
		                                        <label class="col-md-12 control-label">
		                                            <h4 class="text-left">
		                                                @lang('message.2sa.extra-step')
		                                            </h4>
		                                            <br>
		                                            <h4 class="text-left">
		                                                @lang('message.2sa.confirm-message')
		                                                @if (auth()->user()->user_detail->two_step_verification_type == 'phone')
		                                                    {{ str_pad(substr(auth()->user()->phone, -2), strlen(auth()->user()->phone), '*', STR_PAD_LEFT) }}.
		                                                @elseif (auth()->user()->user_detail->two_step_verification_type == 'email')
		                                                    {{ auth()->user()->email }}
		                                                @endif
		                                            </h4>
		                                        </label>
		                                    </div>

		                                    <div class="form-group {{ $errors->has('two_step_verification_code') ? ' has-error' : '' }}">
		                                        <div class="col-md-6">
		                                            <input id="two_step_verification_code" class="form-control" placeholder="Enter the 6-digit code" name="two_step_verification_code"
		                                            oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" type = "number" maxlength = "6" required autofocus/>

		                                            @if ($errors->has('two_step_verification_code'))
		                                                <span class="error">
		                                                    <strong>{{ $errors->first('two_step_verification_code') }}</strong>
		                                                </span>
		                                            @endif
		                                        </div>
		                                    </div>

		                                    <div class="form-group">
		                                        <div class="checkbox icheck" style="margin-left: 15px;">
		                                            <label>
		                                                <input type="checkbox" name="remember_me" id="remember_me">
		                                                <span style="font-size: 16px; font-weight: 600; color: #181818;">&nbsp;&nbsp;&nbsp;@lang('message.2sa.remember-me-checkbox')</span>
		                                            </label>
		                                        </div>
		                                    </div>

		                                    <div class="form-group">
		                                        <div class="col-md-6 col-md-offset-6">
		                                            <button type="submit" class="btn btn-cust verify_code" id="verify_code">@lang('message.2sa.verify')</button>
		                                        </div>
		                                    </div>
		                                </form>
		                            </div>
		                            <!--/card-block-->
		                        </div>
		                    </div>
		                </div>
		                <!--/row-->
		            </div>
		            <!--/col-->
		        </div>
		        <!--/row-->
		    </div>
		</section>

		<section class="contact" id="contact">
		    <div class="contact-content">
		        <div class="container">
		            <div class="row">
		                <div class="col-md-4 col-sm-4">
		                    <div class="contact-detail">
		                        <h2>@lang('message.footer.follow-us')</h2>
		                        <div class="social-icons">
		                            @if(!empty($socialList))
		                                @foreach($socialList as $social)
		                                    <a href="{{ $social->url }}">{!! $social->icon !!}</a>
		                                @endforeach
		                            @endif

		                        </div>
		                    </div>
		                </div>
		                <div class="col-md-4 col-sm-4">
		                    <div class="quick-link">
		                        <h2 style="margin-left: 60px">@lang('message.footer.related-link')</h2>
		                        <ul style="display: grid;grid-template-columns: 170px auto">
		                            <li class="nav-item"><a href="{{url('/')}}"
		                                                    class="nav-link">@lang('message.home.title-bar.home')</a></li>
		                            <li class="nav-item"><a href="{{url('/send-money')}}"
		                                                    class="nav-link">@lang('message.home.title-bar.send')</a></li>
		                            <li class="nav-item"><a href="{{url('/request-money')}}"
		                                                    class="nav-link">@lang('message.home.title-bar.request')</a></li>
		                            @if(!empty($menusFooter))
		                                @foreach($menusFooter as $footer_navbar)
		                                    <li class="nav-item"><a href="{{url($footer_navbar->url)}}"
		                                                            class="nav-link"> {{ $footer_navbar->name }}</a></li>
		                                @endforeach
		                            @endif
		                            <li class="nav-item"><a href="{{url('/developer')}}" class="nav-link">@lang('message.home.title-bar.developer')</a></li>

		                        </ul>
		                    </div>
		                </div>
		                <div class="col-md-4 col-sm-4">
		                    <form class="contact-form-area">
		                        <h2>@lang('message.footer.language')</h2>
		                        <div class="form-group">
		                            <select class="form-control" id="lang">
		                                @foreach (getLanguagesListAtFooterFrontEnd() as $lang)
		                                    <option
		                                        <?= Session::get('dflt_lang') == $lang->short_name ? 'selected' : ''?> value='{{ $lang->short_name }}'> {{ $lang->name }}</option>
		                                @endforeach
		                            </select>
		                        </div>
		                        <div class="playStore">
		                            @foreach(getAppStoreLinkFrontEnd() as $app)
		                                @if (isset($app->logo))
		                                    <a href="{{$app->link}}"><img
		                                                src="{{url('public/uploads/app-store-logos/'.$app->logo)}}"
		                                                class="img-responsive"
		                                                style="padding-left:5px;padding-right: 5px;width:50%; float:left;height: 39px;"/></a>
		                                @else
		                                    <a href="#"><img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}'
		                                                     class="img-responsive" width="120" height="90"
		                                                     style="height: 39px;width:50%; float:left;"/></a>
		                                @endif
		                            @endforeach
		                        </div>
		                    </form>
		                </div>
		            </div>
		        </div>
		    </div>
		</section>

        <footer>
		  <div class="container">
		      <div class="row">
		          <div class="col-md-12">
		              <p class="copyright">Copyright &copy; {{date('Y')}} &nbsp;&nbsp; {{ $company_name }} | All rights reserved</p>
		          </div>
		      </div>
		  </div>
		</footer>
    </body>

    <!--javascript-->
	<script src="{{asset('public/user_dashboard/js/jquery.min.js')}}" type="text/javascript"></script>
	<script src="{{asset('public/user_dashboard/js/bootstrap.min.js')}}" type="text/javascript"></script>
	<script src="{{asset('public/user_dashboard/js/jquery.waypoints.min.js')}}" type="text/javascript"></script>
	<script src="{{asset('public/user_dashboard/js/main.js')}}" type="text/javascript"></script>
	<!-- iCheck -->
	<script src="{{ url('public/user_dashboard/js/iCheck/icheck.min.js') }}" type="text/javascript"></script>
	<!-- fingerprint2 -->
	<script src="{{ url('public/user_dashboard/js/fpjs2/fpjs2.js') }}" type="text/javascript"></script>

	<script type="text/javascript">

		//extra
		    $(function () {
		        $('[data-toggle="tooltip"]').tooltip();
		    });

		    function resizeHeaderOnScroll() {
		        const distanceY = window.pageYOffset || document.documentElement.scrollTop,
		            shrinkOn = 100,
		            headerEl = document.getElementById('js-header');
		        if (headerEl) {
		            if (distanceY > shrinkOn) {
		                headerEl.classList.add("smaller-header");
		                $("#logo_container").attr('src', SITE_URL + '/public/frontend/images/logo_sm.png');
		            } else {
		                headerEl.classList.remove("smaller-header");
		                $("#logo_container").attr('src', SITE_URL + '/public/frontend/images/logo.png');
		            }
		        }
		    }
		    window.addEventListener('scroll', resizeHeaderOnScroll);

		    //Language script
		    $('#lang').on('change', function (e) {
		        e.preventDefault();
		        lang = $(this).val();
		        url = '{{url('change-lang')}}';
		        $.ajax({
		            type: 'get',
		            url: url,
		            async: false,
		            data: {lang: lang},
		            success: function (msg)
		            {
		                if (msg == 1)
		                {
		                    location.reload();
		                }
		            }
		        });
		    });
	    //extra

	    //verifying script - start
		    $(function () {
			    $('input').iCheck({
			        checkboxClass: 'icheckbox_square-blue',
			        radioClass: 'iradio_square-blue',
			        increaseArea: '20%' // optional
			    });
			});

			//verifying on submit
			$('#2sa_form').submit(function(event)
			{
			    event.preventDefault();

			    var token = '{{csrf_token()}}';
			    var two_step_verification_code = $("#two_step_verification_code").val();
			    var remember_me = $("#remember_me").is(':checked');

				//Fingerprint2
				new Fingerprint2().get(function(result, components)
				{
				   $.ajax({
			        method: "POST",
			        url: SITE_URL + "/2fa/verify",
			        cache: false,
			        dataType:'json',
			        data: {
				            "_token": token,
				     		'two_step_verification_code': two_step_verification_code,
				            'remember_me': remember_me,
				            'browser_fingerprint': result,
			        	}
				    })
				    .done(function(data)
				    {
				       	if (data.status == false || data.status == 404)
				        {
				        	//failure
				            $('#message').css('display', 'block');
				            $('#message').html(data.message);
				            $('#message').addClass(data.error);
				            // return false;
				        }
				        else
				        {
				        	// console.log('verified');
				        	//success
				            $('#message').removeClass('alert-danger');
				            $('#message').hide();
				            window.location.href="{{ url('dashboard') }}";
				            // return true;
				        }
				    });
				});
			});
	</script>
</html>



