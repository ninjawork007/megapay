
<?php
$socialList = getSocialLink();
$menusFooter = getMenuContent('Footer');
?>
<section class="contact" id="contact">
    <div class="contact-content">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-3">
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
                <div class="col-md-3 col-sm-3">
                    <div class="quick-link">
                        <h2>@lang('message.footer.related-link')</h2>
                        <ul>
            @if(!empty($menusFooter))
                @foreach($menusFooter as $footer_navbar)
                    <li class="nav-item"><a href="{{url($footer_navbar->url)}}" class="nav-link"> {{ $footer_navbar->name }}</a></li>
                @endforeach
            @endif
                        </ul>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <div class="quick-link">
                        <h2>@lang('message.footer.categories')</h2>
                        <ul>
                            <li><a href="#">Personal</a></li>
                            <li><a href="#">Merchant</a></li>
                            <li><a href="#">Purchase</a></li>
                            <li><a href="#">Help</a></li>
                            <li><a href="#">Support</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <form class="contact-form-area">
                        <h2>@lang('message.footer.language')</h2>
                       <div class="form-group">
                            <select class="form-control" id="">
                                @foreach (getLanguagesListAtFooterFrontEnd() as $lang)
                                    <option {{ Session::get('dflt_lang') == $lang->short_name ? 'selected' : '' }} value='{{ $lang->short_name }}'> {{ $lang->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>