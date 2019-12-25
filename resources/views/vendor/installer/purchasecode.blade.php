@extends('vendor.installer.layout')

@section('content')
  <div class="card">
            <div class="card-content black-text">
                    <div class="center-align">
                        <p class="card-title">{{trans('installer.welcome.verify_code_title')}}</p>
                        <hr>
                    </div>
                     <form class="form-horizontal" action="{{url('install/verify-envato-purchase-code')}}" method="post">
                          @csrf
                          <div class="form-group">
                            <div class="col-md-8 offset-2">
                              <label for="envatopurchasecode">{{trans('installer.welcome.envato_label_text')}}</label>
                              <input type="text" class="form-control" id="envatopurchasecode" name="envatopurchasecode" placeholder="{{trans('installer.welcome.envato_placeholder_text')}}">
                              <span class="text-danger" style="color: red">{{$errors->first('envatopurchasecode')}}</span>
                            </div>
                          </div>
  
                          <div class="card-action">
                              <div class="row">
                                 <div class="left">
                                    <a class="btn waves-effect blue waves-light" href="{{ url('install/permissions') }}">
                                        {{ trans('installer.welcome.back_button') }}
                                        <i class="material-icons left">arrow_back</i>
                                    </a>
                                  </div>
                                  <div class="right">
                                    <button type="submit" class="btn waves-effect blue waves-light">
                                        {{ trans('installer.welcome.verify_button') }}
                                        <i class="material-icons right">send</i>
                                    </button>
                                  </div>
                              </div>
                          </div>
                      </form>
            </div> 
  </div>
@endsection