@extends('admin.layouts.master')

@section('title', 'Payment Methods')

@section('page_content')

    <section class="content">
      <div class="row">
        <div class="col-md-3 settings_bar_gap">
          @include('admin.common.settings_bar')
        </div>
        <div class="col-md-9">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">

              @php $fl = 0; @endphp
                @foreach($tab_names as $id => $name)
                  @if($fl == 0)
                    <li class="active"><a href="#{{$id}}" data-toggle="tab">{{$name}}</a></li>

                    @php $fl=1; @endphp
                  @else

                    <li><a href="#{{$id}}" data-toggle="tab">{{$name}}</a></li>

                  @endif
                @endforeach

            </ul>
            <div class="tab-content">

              @php $fl = 0; @endphp

              @foreach($tab_forms as $id => $form)
                <div class="tab-pane {{ ($fl == 0)? 'active':''}}" id="{{$id}}">

                  <form id="{{ $form['form_id'] or ''}}"
                  method="post" action="{{ $form['action'] or ''}}"
                  class="form-horizontal {{ $form['form_class'] or '' }}" {{ isset($form['form_type']) && $form['form_type'] == 'file'? "enctype=multipart/form-data":"" }}>
                    {{ csrf_field() }}

                    <div class="box-body">
                      @foreach($form['fields'] as $field)
                        @include("admin.common.fields.".$field['type'], ['field' => $field])
                      @endforeach

                      @if(Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_payment_methods'))
                      <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-6">
                          {{-- <button type="button" class="btn btn-default">Cancel</button> --}}
                          {{-- <a class="btn btn-default" href="{{ redirect()->back() }}">Cancel<a> --}}
                          <button type="submit" class="btn btn-primary pull-right">Submit</button>
                        </div>
                      </div>
                      @endif

                    </div>
                  </form>
                </div>

                @php $fl = 1; @endphp

              @endforeach
              <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
          </div>
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
@endsection