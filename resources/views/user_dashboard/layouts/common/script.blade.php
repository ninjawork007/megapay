<script src="{{asset('public/user_dashboard/js/jquery.min.js')}}" type="text/javascript"></script>
<!-- popper.min.js must place before bootstrap.min.js, else won't work-->
<script src="{{asset('public/user_dashboard/js/popper.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/jquery.waypoints.min.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/main.js')}}" type="text/javascript"></script>
<script src="{{asset('public/user_dashboard/js/moment.js')}}" type="text/javascript"></script>

{!! getGoogleAnalyticsTrackingCode() !!}

<script type="text/javascript">
    $('#delete-warning-modal').on('show.bs.modal', function (e) {
        $message  = $(e.relatedTarget).attr('data-message');
        $(this).find('.modal-body p').text($message);
        $title    = $(e.relatedTarget).attr('data-title');
        $(this).find('.modal-title').text($title);

        // Pass form reference to modal for submission on yes/ok
        var form  = $(e.relatedTarget).closest('form');
        $(this).find('.modal-footer #delete-modal-yes').data('form', form);
    });

    $('#delete-warning-modal').find('.modal-footer #delete-modal-yes').on('click', function(e){
        $(this).data('form').submit();
    });
</script>

<script type="text/javascript">

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function log(log) {
        console.log(log);
    }

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();

        // console.log($('#subStringUserName').html().length);
        if ($('#subStringUserName').html().length > 20) {
             $('#subStringUserName').tooltip({placement: "left"}); //subStringUserName tooltip
        }
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

    //Language - user_dashboard
    $('#lang').on('change', function (e) {
        e.preventDefault();
        lang = $(this).val();
        url = '{{url('change-lang')}}';
        $.ajax({
            type: 'get',
            url: url,
            async: false,
            data: {lang: lang},
            success: function (msg) {
                if (msg == 1) {
                    location.reload();
                }
            }
        });
    });
</script>