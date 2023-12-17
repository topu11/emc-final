@extends('layouts.landing')

@section('landing')
    <!--begin::Card-->
    <div class="container">
        <div class="row" style="margin-top: 85px">
            <div class="col-md-12">
                <div class="card">
                    <div class="text-center p-5 m-5">
                     <h1>{{ $message1 }}</h1>
                    <p>{{ $message2 }}</p>
                    <a href="{{ $callbackurl }}">Click here</a>
                    </div>
                 </div>
            </div>
        </div>
    </div>
    

    <!--end::Card-->

@endsection


{{-- Includable CSS Related Page --}}
@section('styles')
    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Page Vendors Styles-->
@endsection

{{-- Scripts Section Related Page --}}
@section('scripts')
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('js/pages/crud/datatables/advanced/multiple-controls.js') }}"></script>
    <!--end::Page Scripts-->
@endsection