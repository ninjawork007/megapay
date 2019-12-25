@extends('frontend.layouts.app')
@section('content')
@include('frontend.layouts.common.content_title')
    <!--End banner Section-->
    <!--Start Section-->
    <section class="section-02 history padding-30">
        <div class="container">
            <div class="row">
                @include('frontend.layouts.common.dashboard_menu')
            </div>
            <!--/row-->
        </div>
    </section>
    <!--End Section-->

@endsection
@section('js')
<script>

</script>
@endsection