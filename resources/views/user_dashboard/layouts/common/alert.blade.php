<div class="flash-container">
      @if(Session::has('message'))
          <div class="alert {{ Session::get('alert-class') }} text-center" style="margin-bottom:10px;" role="alert">
            {{ Session::get('message') }}
            <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
          </div>
      @endif

      @if(!empty($error))
          <div class="alert alert-danger text-center" style="margin-bottom:10px;" role="alert">
            {{ $error }}
            <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
          </div>
      @endif

      @if($errors->any())
          <div class="alert alert-danger text-center" style="margin-bottom:10px;" role="alert">
              <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
          @foreach ($errors->all() as $error)
                  {{ $error }} <br/>
              @endforeach
          </div>
      @endif

      @if(Session::has('success'))
          <div class="alert alert-success text-center" style="margin-bottom:10px;" role="alert">
            {{ Session::get('success') }}
            <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
          </div>
      @endif

      @if(Session::has('error'))
          <div class="alert alert-danger text-center" style="margin-bottom:10px;" role="alert">
            {{ Session::get('error') }}
            <a href="#" style="float:right;" class="alert-close" data-dismiss="alert">&times;</a>
          </div>
      @endif
  </div>