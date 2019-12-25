

<script src="{{asset('public/frontend/js/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/frontend/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/frontend/js/jquery.waypoints.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/frontend/js/main.js')}}" type="text/javascript"></script>
<script src="{{asset('public/frontend/js/moment.js')}}" type="text/javascript"></script>

<!--Google Analytics Tracking Code-->
{!! getGoogleAnalyticsTrackingCode() !!}

<script type="text/javascript">
</script>

<script type="text/javascript">

    function log(log) {
        console.log(log);
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

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
    $('#lang').on('change', function (e)
    {
        e.preventDefault();
        lang = $(this).val();
        url = '{{url('change-lang')}}';
        $.ajax({
            type: 'get',
            url: url,
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
</script>