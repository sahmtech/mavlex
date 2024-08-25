<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,600" rel="stylesheet" type="text/css"> -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vendor.css') }}">

    <!-- Styles -->
    <style>
        * {
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
        }

        .dropdown-menu>li>a {
            font-weight: bold;
        }

        .checkbox label,
        .checkbox-inline,
        .radio label,
        .radio-inline {
            font-weight: bold;

        }

        .fa-folder:before {
            color: #ffd400 !important;
            /* background-color:#ffd400; */
        }

        .fa-arrow-alt-circle-right:before {
            color: #3936f5;
        }

        .dataTables_filter .input-sm {
            border-radius: 6px !important;
            width: 105% !important;
        }

        .dataTables_length .input-sm {
            padding: 1px;
            border-radius: 5px;
            margin: 0px 7px;
        }

        /* .dt-buttons.btn-group .input-sm {
            background: none;
            border: 0px;
            font-size: 27px;
        } */

        body {
            min-height: 100vh;
            background-color: #243949;
            color: #fff;
            /* background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.12'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); */
            background-image: linear-gradient(to right, #6b7399, #243949);
        }

        .navbar-default {
            background-color: transparent;
            border: none;
        }

        .navbar-static-top {
            margin-bottom: 19px;
        }

        .navbar-default .navbar-nav>li>a {
            color: #fff;
            font-weight: 600;
            font-size: 15px
        }

        .navbar-default .navbar-nav>li>a:hover {
            color: #ccc;
        }

        .navbar-default .navbar-brand {
            color: #ccc;
        }
    </style>
</head>

<body>
    @include('layouts.partials.home_header')
    <div class="container">
        <div class="content">
            @yield('content')
        </div>
    </div>
    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    @yield('javascript')
</body>

</html>
