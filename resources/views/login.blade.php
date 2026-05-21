<!DOCTYPE html>
<html class="bg-dark" lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>@yield('title', env('APP_NAME').' | Panel')</title>
    <meta name="description" content="@yield('description',  env('APP_NAME').' | Panel')">
    <link rel="icon" type="image/png" href="{{ asset('img/icon/favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/icon/favicon.svg')}}" />
    <link rel="shortcut icon" href="{{ asset('img/icon/favicon.ico')}}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/icon/apple-touch-icon.png')}}" />
    <meta name="apple-mobile-web-app-title" content="{{ env('APP_NAME')}}" />
    <link rel="manifest" href="{{ asset('img/icon/site.webmanifest')}}" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="white-translucent" media="(prefers-color-scheme: light)">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b0b18" media="(prefers-color-scheme: dark)">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{route('home')}}/">
    <meta name="api-url" content="{{route('api')}}/">


    @vite(['resources/scss/app.scss', 'resources/js/app.js', 'resources/css/theme.css', 'resources/css/login.css',
    'resources/js/login.js',
    ])


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
    <div id="sidebar-overlay" class="d-none"></div>

    <div class="h-custom-screen p-0 h-100 bg-dark">
        <div class="row g-0 h-100">

            <!-- Panel izquierdo -->
            <div class="col-lg-6 text-bg-dark marketing-panel d-none d-lg-flex flex-column justify-content-between p-5">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{asset('img/logo.png')}}" width="240" alt="">

                </div>

                <div class="my-auto">
                    <h1 class="display-5 fw-bold mb-3">
                        Compra de forma responsable con {{env("APP_NAME")}}.
                    </h1>
                    <p class="fs-5 ">
                        Accede a tu cuenta para ver tus pedidos, guardar tus productos favoritos
                        y seguir comprando de manera sustentable.
                    </p>
                </div>

                <div class="d-flex justify-content-between small text-muted">
                    <span>© 2025 {{env("APP_NAME")}}. Todos los derechos reservados.</span>
                    <a href="#" class="text-decoration-none">
                        Aviso de privacidad
                    </a>
                </div>
            </div>

            <!-- Panel derecho -->
            <div class="col-lg-6 col-12 login-panel px-4 card-dark">
                <div class="container col-lg-8 col-xl-6 col-12">

                    <div class="text-center d-lg-none mb-2">
                        <img src="{{asset('img/icon.png')}}" width="90" alt="">

                    </div>

                    <div class="text-center mb-4 d-none d-lg-block">
                        <h2 class="fw-bold mb-2 text-dark">
                            Inicia sesión
                        </h2>
                        <p class="text-muted">
                            Accede a tu cuenta para continuar con tu compra.
                        </p>
                    </div>

                    <div class="text-center mb-4 d-lg-none">
                        <small class="text-muted">
                            Accede a tu cuenta para continuar con tu compra.
                        </small>
                    </div>

                    <form action="" method="POST" id="login-form">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold small text-dark">
                                Correo electrónico
                            </label>
                            <input
                                type="email"
                                class="text-dark form-control card-dark border border-dark"
                                id="email"
                                name="email"
                                placeholder="correo@ejemplo.com"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold small text-dark">
                                Contraseña
                            </label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    class="text-dark form-control card-dark border border-dark"
                                    id="password"
                                    name="password"
                                    placeholder="Ingresa tu contraseña"
                                    required>
                                <span class="input-group-text text-bg-dark border border-dark" style="cursor: pointer;">
                                    <i class="fa-regular fa-eye"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end mb-4 small">

                            <a data-bs-toggle="modal" data-bs-target="#forgetModal" class="text-decoration-none text-primary text-end fw-semibold">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        <div id="error-message" class="alert alert-danger" style="display: none;"></div>

                        <div class="row g-4">
                            <div class="col-12">

                                <button type="submit" class="btn btn-primary w-100">
                                    Entrar a mi cuenta
                                </button>
                            </div>
                            <!-- <div class="col-12">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-brands fa-google me-2"></i>Entrar con Google
                                </button>
                            </div> -->
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <div class="fixed-top">
        <div class="p-3 float-end">
            <a class=" position-relative">
                <div class="theme-switch ">
                    <input type="checkbox" id="themeToggle">
                    <label for="themeToggle" class="switch border border-primary">
                        <span class="icon moon"><i class="fas fa-moon"></i></span>
                        <span class="icon sun"><i class="fas fa-sun"></i></span>
                        <span class="slider"></span>
                    </label>
                </div>
            </a>
        </div>

    </div>
    @include("components.forget")
    @include("components.alert")

</body>

</html>