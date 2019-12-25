<!-- Bootstrap 3.3.7 -->
<script src="{{ asset('public/backend/bootstrap/dist/js/bootstrap.min.js') }}" type="text/javascript"></script>

<!-- Select2 -->
<script src="{{ asset('public/backend/select2/select2.full.min.js') }}" type="text/javascript"></script>

<!-- moment -->
<script src="{{ asset('public/backend/moment/moment.js') }}" type="text/javascript"></script>

<!-- AdminLTE App -->
<script src="{{ asset('public/dist/js/app.min.js') }}" type="text/javascript"></script>

<!-- Page script -->

<!-- ajaxSetup -->
<script type="text/javascript">
    $.ajaxSetup({
     headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
  });
</script>

<script>

  function log(log)
  {
    console.log(log);
  }

  // delete script for href
  $(document).on('click', '.delete-warning', function(e){
    e.preventDefault();
    var url = $(this).attr('href');
    $('#delete-modal-yes').attr('href', url);
    $('#delete-warning-modal').modal('show');
  });

  //delete script for buttons
  $('#confirmDelete').on('show.bs.modal', function (e) {
      $message  = $(e.relatedTarget).attr('data-message');
      $(this).find('.modal-body p').text($message);
      $title    = $(e.relatedTarget).attr('data-title');
      $(this).find('.modal-title').text($title);

      // Pass form reference to modal for submission on yes/ok
      var form  = $(e.relatedTarget).closest('form');
      $(this).find('.modal-footer #confirm').data('form', form);
  });

  $('#confirmDelete').find('.modal-footer #confirm').on('click', function(){
      $(this).data('form').submit();
  });

  // language
  $('.lang').on('click', function() {
      var lang = $(this).attr('id');
      var url = "{{url('change-lang')}}";
      var token = "{{csrf_token()}}";

      console.log("on script file");
      $.ajax({
          url   :url,
          async : false,
          data:{
           // data that will be sent
              _token:token,
              lang:lang
          },
          // type of submision
          type:"POST",
          success:function(data){
              console.log("sucess "+data);
              if(data == 1) {
                  location.reload();
              }
          },
          error: function(xhr, desc, err) {
              return 0;
          }
      });
  });
</script>


@yield('body_script')
