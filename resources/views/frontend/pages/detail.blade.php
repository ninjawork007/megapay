@extends('frontend.layouts.app')
@section('content')
    <section class="inner-banner">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1> {{ $pageInfo->name }} </h1>
                </div>
            </div>
        </div>
    </section>
    <!--End banner Section-->

    <!--Start Section-->
    <section class="section-01 padding-10">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {!! $pageInfo->content !!}
                </div>
                <!--/col-->
            </div>
            <!--/row-->
        </div>
    </section>

@endsection
@section('js')
<script>

</script>
@endsection
