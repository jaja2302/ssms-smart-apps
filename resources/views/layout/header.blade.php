<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> DASHBOARD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/CBI-logo.png') }}">

    {{--
    <link href="{{ asset('fontawesome6/css/fontawesome.css') }}"> --}}


    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css"
        integrity="sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP" crossorigin="anonymous">

    <link href="{{asset('fontawesome6/css/all.css')}}" rel="stylesheet">

    <link rel="stylesheet" href="//cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" defer></script>
    {{-- <script src="https://kit.fontawesome.com/3d2c665316.js" crossorigin="anonymous"></script> --}}


    {{--
    <link href="{{ asset('fontawesome6/css/solid.css') }}" rel="stylesheet"> --}}


    {{--
    <link href="{{ asset('fontawesome6/css/solid.css') }}" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css"
        integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.2/dist/leaflet.js"
        integrity="sha256-o9N1jGDZrf5tS+Ft4gbIK7mYMipq9lqpVJ91xHSyKhg=" crossorigin=""></script>

    <!-- Load Esri Leaflet from CDN -->
    <script src="https://unpkg.com/esri-leaflet@3.0.8/dist/esri-leaflet.js"
        integrity="sha512-E0DKVahIg0p1UHR2Kf9NX7x7TUewJb30mxkxEm2qOYTVJObgsAGpEol9F6iK6oefCbkJiA4/i6fnTHzM6H1kEA=="
        crossorigin=""></script>

    <!-- Load Esri Leaflet Vector from CDN -->
    <script src="https://unpkg.com/esri-leaflet-vector@4.0.0/dist/esri-leaflet-vector.js"
        integrity="sha512-EMt/tpooNkBOxxQy2SOE1HgzWbg9u1gI6mT23Wl0eBWTwN9nuaPtLAaX9irNocMrHf0XhRzT8B0vXQ/bzD0I0w=="
        crossorigin=""></script>
    <link rel="stylesheet" href="{{ asset('css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/jquery.fancybox.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css')}}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">


    <!--download-->
    <link href="{{ asset('css/css.css') }}" rel="stylesheet">

    <!--download-->
    <script type="text/javascript" src="{{ asset('js/loader.js') }}"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.11.5/datatables.min.css" />

    <!--download-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/buttons.dataTables.min.css') }}" />
    <!--download-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.dataTables.min.css') }}" />


    <style type="text/css">
        .center {
            margin: auto;
            height: 500px;
            width: 70%;
            padding: 10px;
            text-align: center;
        }

        .tengah {
            vertical-align: middle;
        }

        .hijau {
            background-color: #00621A;
            color: white;
        }

        .biru {
            background-color: #001494;
            color: white;
        }

        .merah {
            background-color: red;
            color: red;
        }
    </style>

</head>



<body class="hold-transition sidebar-mini sidebar-collapse layout-fixed layout-navbar-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="hover"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a class="nav-link">Selamat datang {{ Auth::user()->nama_lengkap }} !</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item d-none d-sm-inline-block">
                    <a class="nav-link"></a>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-light-primary elevation-4">
            <a href="{{ asset('dashboard') }}" class="brand-link">
                <img src="{{ asset('img/CBI-logo.png') }}" alt="Covid Tracker"
                    class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Dashboard</span>
            </a>
            <div class="sidebar">

                <nav class="" style="height: 100%">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false" style="height: 100%">
                        <!-- USER LAB -->

                        <!-- TABEL -->
                        {{-- <li class="nav-item">
                            <!-- uses solid style -->
                            <a href="{{ asset('/dashboard_taksasi') }}" class="nav-link">
                                <i class="nav-icon fa-solid fa-file"></i>
                                <p>
                                    Taksasi Estate
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ asset('/dashboard_taksasi_afdeling') }}" class="nav-link">
                                <i class="nav-icon fa fa-file"></i>
                                <p>
                                    Taksasi Afdeling
                                </p>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ asset('/history_taksasi') }}" class="nav-link">
                                <i class="nav-icon fa-solid fa-clock-rotate-left"></i>
                                <p>
                                    History Taksasi
                                </p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ asset('/dashboard_pemupukan') }}" class="nav-link">
                                <i class="nav-icon fa fa-seedling"></i>
                                <p>
                                    Dashboard Pemupukan
                                </p>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="{{ asset('/maps') }}" class="nav-link">
                                <i class="nav-icon fa-solid fa-map-location-dot"></i>
                                <p>
                                    Data Pokok Kuning
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ asset('/mapsTest') }}" class="nav-link">
                                <i class="nav-icon fa-solid fa-map-location-dot"></i>
                                <p>
                                    Testing map Estate
                                </p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="{{ asset('/field-inspection') }}" class="nav-link">
                                <i class="nav-icon fa fa-book"></i>
                                <p>
                                    Field Inspection
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ asset('/vm') }}" class="nav-link">
                                <i class="nav-icon fa fa-car"></i>
                                <p>
                                    Vehicle Management
                                </p>
                            </a>
                        </li> --}}

                        <li class="nav-item fixed-bottom mb-3" heig style="position: absolute;">
                            <a href="{{ asset('/logout') }}" class="nav-link ">
                                <i class="nav-icon fa fa-sign-out-alt"></i>
                                <p>
                                    Logout
                                </p>
                            </a>
                        </li>


                    </ul>
                </nav>
            </div>
        </aside>