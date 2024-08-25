<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>

    @include('layouts.partials.css')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif

    <div class="container-fluid">
        {{-- <div class="row eq-height-row"> --}}
        {{-- <div class="col-md-12 col-sm-12 hidden-xs left-col eq-height-col" >
                <div class="left-col-content login-header"> 
                    <div style="margin-top: 50%;">
                    <a href="/">
                    @if (file_exists(public_path('uploads/logo.png')))
                        <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                    @else
                       {{ config('app.name', 'ultimatePOS') }}
                    @endif 
                    </a>
                    <br/>
                    @if (!empty(config('constants.app_title')))
                        <small>{{config('constants.app_title')}}</small>
                    @endif
                    </div>
                </div>
            </div> --}}
        {{-- <div class="col-md-12 col-sm-12 col-xs-12 right-col eq-height-col"> --}}
        <div class="container">
            <div class="row">

                <div class="col-sm-3">
                    <select class="form-control input-sm" id="change_lang" style="margin: 10px;">
                        @foreach (config('constants.langs') as $key => $val)
                            <option value="{{ $key }}" @if ((empty(request()->lang) && config('app.locale') == $key) || request()->lang == $key) selected @endif>
                                {{ $val['full_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="padding-top: 50px;">
                <div class="row " style="border-radius: 22px;box-shadow: 1px 1px 4px rgb(0 0 0 / 74%);">
                    <div class="col-sm-12 col-md-8 login-col-right-logo-title">
                        @yield('content')
                    </div>

                    @if (app()->getLocale() == 'ar')
                        <div class="login-col-left-logo-title-ar col-md-4 col-sm-0">
                        @else
                            <div class="login-col-left-logo-title-en col-md-4 col-sm-0 col-xs-0">
                    @endif


                    <div class="row">
                        <div class="col">
                            <h3>

                                @if (file_exists(public_path('uploads/logo.png')))
                                    <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                                @else
                                    <p class="form-header"
                                        style="color: #ffffff;
                                font-size: x-large;
                                font-weight: bold;
                                margin: revert-layer;">
                                        {{ config('app.name', 'ultimatePOS') }}</p>
                                @endif
                            </h3>
                        </div>
                        <div class="col">
                            @if (!empty(config('constants.app_title')))
                                <small>{{ config('constants.app_title') }}</small>
                            @endif
                        </div>

                    </div>


                    <br />

                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
            <div class="col-md-12 col-xs-12" style="text-align: left;">
                <select class="form-control input-sm" id="change_lang" style="margin: 10px;">
                    @foreach (config('constants.langs') as $key => $val)
                        <option value="{{ $key }}" @if ((empty(request()->lang) && config('app.locale') == $key) || request()->lang == $key) selected @endif>
                            {{ $val['full_name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div> --}}

    {{-- <div class="row" style="margin: 103px;
            border: 1px solid;
            border-radius: 33px;">

            <div class="col-md-10 col-xs-10">
                @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                    <!-- Register Url -->
                    @if (config('constants.allow_registration'))
                        <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif"
                            class="btn bg-maroon btn-flat"><b>{{ __('business.not_yet_registered') }}</b>
                            {{ __('business.register_now') }}</a>
                        <!-- pricing url -->
                        @if (Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                            &nbsp; <a
                                href="{{ action([\Modules\Superadmin\Http\Controllers\PricingController::class, 'index']) }}">@lang('superadmin::lang.pricing')</a>
                        @endif
                    @endif
                @endif
                @if ($request->segment(1) != 'login')
                    &nbsp; &nbsp;<span class="text-white">{{ __('business.already_registered') }} </span><a
                        href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login']) }}@if (!empty(request()->lang)) {{ '?lang=' . request()->lang }} @endif">{{ __('business.sign_in') }}</a>
                @endif
                @yield('content')
            </div>
            <div class="col-md-2 col-xs-2"
                style="height: 49vh;
                display: flex;
                text-align: center;
                align-items: center;">
                <div class="row">
                    <div class="col">
                   
                    </div>
                    <div class="col">
                      
                    </div>

                </div>


            </div>

        </div>
 --}}


    {{-- </div> --}}
    {{-- </div> --}}
    </div>


    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>

    @yield('javascript')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });
    </script>
</body>

</html>
