<section class="dashboard-banner">
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <h1><i class="fa fa-{{$icon}} aria-hidden="true"></i> {{ $content_title }}</h1>
            </div>
            <div class="col-md-3">
                <?php
                $wallets = userWallets();
                $ulstart = '<ul>';
                $ulend = '<ul>';
                $li = '';
                
                if($wallets->count()>0){
                    foreach ($wallets as $key => $result) {
                    $li .="<li style='text-align:left'>".$result->currency->code.' '. decimalFormat($result->balance)."</li>";
                    }
                }

                $myWallet = $ulstart.$li.$ulend;

                ?>
                <div class="banner-amount-icon" data-toggle="tooltip" data-placement="right" data-html="true" title="{{$myWallet}}">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </div>
                <div class="banner-amount">
                    <h4 style="color:#ffffff;">Available balance</h4>
                    <h2 style="color:#ffffff;"> {{ current_balance() }} </h2>
                </div>
            </div>
        </div>
    </div>
</section>