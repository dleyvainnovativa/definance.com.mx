<nav class="navbar navbar-expand-lg main-navbar px-4 sticky-top bg-dark  border-bottom border-dark card-dark ">
    <button class="btn btn-dark card-dark border-0" id="sidebarToggle"><i class="fas fa-bars text-dark"></i></button>

    <div class="ms-auto d-flex align-items-center gap-3">
        <a class="btn btn-outline-primary position-relative" data-bs-toggle="modal" data-bs-target="#calculatorModal">
            <i class="fas fa-calculator"></i>
        </a>
        <a id="enable-notifications-btn" href="{{route('entry')}}" class="btn btn-outline-primary position-relative" title="Enable Notifications">
            <i class="fas fa-plus"></i>
        </a>
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
        <div class="dropdown profile">
            <a class="d-flex align-items-center dropdown-toggle text-decoration-none" data-bs-toggle="dropdown">
                <div class="btn btn-outline-primary rounded-circle">
                    <i class="fa-regular fa-user"></i>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end mt-3 p-0 overflow-hidden card-dark border border-dark text-dark" style="min-width: 260px;">

                <!-- Profile Header -->
                <li class="px-3 py-3 border-bottom border-dark">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-bold">{{ session('user_name') }}</div>
                            <small class="text-muted">{{ session('user_email') }}</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mt-2">
                        <span class="badge bg-success-subtle text-success">
                            <i class="fas fa-circle me-1" style="font-size:8px;"></i>
                            Activo
                        </span>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-3 text-dark" href="#">
                        <i class="fas fa-user text-primary me-3"></i> Mi perfil
                    </a>
                </li>

                <li class="border-top border-dark">
                    <a class="dropdown-item text-danger d-flex align-items-center py-3 text-dark" href="{{route('logout')}}">
                        <i class="fas fa-sign-out-alt text-primary me-3"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
{{-- Add this somewhere prominent, like at the top of your content section --}}
<div id="notification-alert" class="alert alert-warning d-none" role="alert">
    <strong>Notifications are blocked.</strong> To receive new booking alerts, please enable notifications for this site in your browser settings.
</div>