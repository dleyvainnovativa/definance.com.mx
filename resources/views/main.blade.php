<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', env('APP_NAME').' | LandingPage')</title>
    <meta name="description" content="@yield('description',  env('APP_NAME').' | LandingPage')">
    <link rel="icon" type="image/png" href="{{ asset('img/icon/favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/icon/favicon.svg')}}" />
    <link rel="shortcut icon" href="{{ asset('img/icon/favicon.ico')}}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/icon/apple-touch-icon.png')}}" />
    <meta name="apple-mobile-web-app-title" content="{{ env('APP_NAME')}}" />
    <link rel="manifest" href="{{ asset('img/icon/site.webmanifest')}}" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="app-url" content="{{route('home')}}/">
    <meta name="api-url" content="{{route('api')}}/">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Light mode -->
    <meta name="apple-mobile-web-app-status-bar-style" content="white-translucent" media="(prefers-color-scheme: light)">

    <!-- Dark mode -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" media="(prefers-color-scheme: dark)">

    <!-- <meta name="theme-color" content="#ffffff"> -->
    <!-- Light mode -->
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">

    <!-- Dark mode -->
    <meta name="theme-color" content="#0b0b18" media="(prefers-color-scheme: dark)">

    <!-- Vite Assets -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js', 'resources/css/theme.css'])

    <!-- Font Awesome (your preload is fine) -->
    <script preload src="https://kit.fontawesome.com/d544c5e79c.js" crossorigin="anonymous"></script>
    <script>
        (function() {
            try {
                const theme = localStorage.getItem("theme");
                if (theme === "light") {
                    document.documentElement.classList.add("theme-light");
                }
            } catch (e) {}
        })();
    </script>
</head>

<body class="bg-dark">
    <div id="wrapper" class="text-bg-dark ">
        <div id="sidebar-overlay" class="d-none"></div>
        @include('components.sidebar')
        <div id="page-content-wrapper" class="bg-dark">
            @include('components.header')
            <main class="container-fluid p-4 bg-dark mb-5">
                @yield('content')

            </main>

        </div>
    </div>
    @include("modals.calculator")
    @include("components.alert")
    @include("components.popup")
    <script>
        let tableOptions = {
            // 1. Custom Loading Template
            loadingTemplate: function(message) {
                return `
                    <div class="d-flex flex-column align-items-center justify-content-center p-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <span class="text-muted">Cargando, por favor espere...</span>
                    </div>
                `;
            },
            iconsPrefix: 'fa', // Tell Bootstrap Table to use the 'fa' class prefix
            icons: {
                paginationSwitchDown: 'fa-caret-square-down',
                paginationSwitchUp: 'fa-caret-square-up',
                refresh: 'fa-sync-alt',
                toggleOff: 'fa-toggle-off',
                toggleOn: 'fa-toggle-on',
                columns: 'fa-th-list',
                detailOpen: 'fa-plus',
                detailClose: 'fa-minus',
                fullscreen: 'fa-expand-arrows-alt',
                search: 'fa-search',
                clearSearch: 'fa-trash'
            }
        };

        window.tableOptions = tableOptions;
    </script>
</body>

</html>