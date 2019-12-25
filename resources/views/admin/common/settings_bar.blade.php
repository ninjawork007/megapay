<div class="box box-info box_info">
    <div class="panel-body">
        <h4 class="all_settings">
            Manage Settings
        </h4>
        <ul class="nav navbar-pills nav-tabs nav-stacked no-margin" role="tablist">

            {{-- @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_general_setting')) --}}
                <li class="{{ (Route::current()->uri() == 'admin/settings') ? 'active' : '' }}">
                    <a data-group="settings" href="{{ url('admin/settings') }}">
                        <i class="glyphicon glyphicon-cog">
                        </i>
                        <span>
                            General
                        </span>
                    </a>
                </li>
            {{-- @endif --}}

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_social_links'))
                <li <?= $menu == 'social_links' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/social_links') }}">
                        <i class="fa fa-share-alt">
                        </i>
                        <span>
                            Social Links
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_api_credentials'))
                <li <?= $menu == 'api_informations' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/api_informations') }}">
                        <i class="fa fa-key">
                        </i>
                        <span>
                            API Credentials
                        </span>
                    </a>
                </li>
            @endif



            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_appstore_credentials'))
                <li <?= $menu == 'app-store-credentials' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/app-store-credentials') }}">
                        <i class="fa fa-key">
                        </i>
                        <span>
                            App Store Credentials
                        </span>
                    </a>
                </li>
            @endif


            <!--@if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_payment_methods'))
                <li <?= $menu == 'payment_methods' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/payment_methods') }}">
                        <i class="fa fa-cc-visa">
                        </i>
                        <span>
                            Payment Methods
                        </span>
                    </a>
                </li>
            @endif
        -->

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_email_setting'))
                <li <?= $menu == 'email' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/email') }}">
                        <i class="fa fa-envelope">
                        </i>
                        <span>
                            Email Settings
                        </span>
                    </a>
                </li>
            @endif


            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_sms_setting'))
                <li <?= $menu == 'sms' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/sms') }}">
                        <i class="glyphicon glyphicon-phone"></i>
                        <span>
                            SMS Settings
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_country'))
                <li <?= $menu == 'country' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/country') }}">
                        <i class="fa fa-flag">
                        </i>
                        <span>
                            Countries
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_language'))
                <li <?= $menu == 'language' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="language" href="{{ url('admin/settings/language') }}">
                        <i class="fa fa-language">
                        </i>
                        <span>
                            Languages
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_merchant_group'))
                <li <?= $menu == 'merchant_group' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/merchant-group') }}">
                        <i class="fa fa-user-secret"></i>
                        <span>
                            Merchant Packages
                        </span>
                    </a>
                </li>
            @endif


            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_group'))
                <li <?= $menu == 'user_role' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/user_role') }}">
                        <i class="fa fa-object-group"></i>
                        <span>
                            User Groups
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_role'))
                <li <?= $menu == 'role' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="permissions_roles" href="{{ url('admin/settings/roles') }}">
                        <i class="fa fa-key"></i>
                        <span>
                            Roles &amp; Permissions
                        </span>
                    </a>
                </li>
            @endif

            <!-- @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_fees'))
                <li <?= $menu == 'fee' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/fees') }}">
                        <i class="fa fa-calculator">
                        </i>
                        <span>
                            Fees
                        </span>
                    </a>
                </li>
            @endif -->

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_database_backup'))
                <li <?= $menu == 'backup' ? ' class="treeview active"' : 'treeview'?>>
                    <a href="{{ url('admin/settings/backup') }}">
                        <i class="fa fa-database">
                        </i>
                        <span>
                            Database Backup
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_meta'))
                <li <?= $menu == 'meta' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="metas" href="{{ url('admin/settings/metas') }}">
                        <i class="glyphicon glyphicon-info-sign">
                        </i>
                        <span>
                            Metas
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_page'))
                <li <?= $menu == 'pages' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="metas" href="{{ url('admin/settings/pages') }}">
                        <i class="fa fa-pagelines"></i>
                        <span>
                            Pages
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_enable_woocommerce'))
                <li <?= $menu == 'enablewoocommerce' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="preference" href="{{ url('admin/settings/enable-woocommerce') }}">
                        <i class="fa fa-shopping-cart"></i>
                        <span>
                            Enable WooCommerce
                        </span>
                    </a>
                </li>
            @endif

            @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'view_preference'))
                <li <?= $menu == 'preference' ? ' class="treeview active"' : 'treeview'?>>
                    <a data-group="preference" href="{{ url('admin/settings/preference') }}">
                        <i class="fa fa-cogs">
                        </i>
                        <span>
                            Preferences
                        </span>
                    </a>
                </li>
            @endif

        </ul>
    </div>
</div>
