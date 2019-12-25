<?php
$socialList = getSocialLink();
$menusFooter = getMenuContent('Footer');
?>

<section class="contact" id="contact">
    <div class="contact-content">
        <div class="container">
            <div class="row">
<!--                 <div class="col-md-4 col-sm-4">
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
                </div> -->
                <div class="col-md-2 col-sm-2"></div>
                <div class="col-md-4 col-sm-4">
                    @if (request()->path() != 'merchant/payment')
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
                    @endif
                </div>
                <div class="col-md-4 col-sm-4">
                    <form class="contact-form-area">
                        <h2>@lang('message.footer.language')</h2>
                        <div class="form-group">
                            <select class="form-control" id="lang">
                                @foreach (getLanguagesListAtFooterFrontEnd() as $lang)
                                    <option {{ Session::get('dflt_lang') == $lang->short_name ? 'selected' : '' }} value='{{ $lang->short_name }}'> {{ $lang->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="playStore">
                            @foreach(getAppStoreLinkFrontEnd() as $app)
                                @if (isset($app->logo))
                                    <a href="{{$app->link}}"><img src="{{url('public/uploads/app-store-logos/'.$app->logo)}}" class="img-responsive" style="padding-left:5px;padding-right: 5px;width:50%; float:left;height: 39px;"/></a>
                                @else
                                    <a href="#"><img src='{{ url('public/uploads/app-store-logos/default-logo.jpg') }}' class="img-responsive" width="120" height="90" style="height: 39px;width:50%; float:left;"/></a>
                                @endif
                            @endforeach
                        </div>
                    </form>
                </div>
                <div class="col-md-2 col-sm-2"></div>
                
            </div>
        </div>
    </div>
</section>
