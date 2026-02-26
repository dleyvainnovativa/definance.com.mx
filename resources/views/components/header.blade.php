<nav class="navbar navbar-expand-lg main-navbar px-4 sticky-top bg-dark  border-bottom border-dark card-dark ">
    <button class="btn btn-dark card-dark border-0" id="sidebarToggle"><i class="fas fa-bars text-dark"></i></button>

    <div class="ms-auto d-flex align-items-center gap-3">
        <a id="enable-notifications-btn" href="{{route('entry')}}" class="btn btn-outline-primary position-relative" title="Enable Notifications">
            <i class="fas fa-plus"></i>
        </a>
        <a class=" position-relative">
            <button id="themeToggle" class="btn btn-primary">
                <i class="fas fa-sun"></i>
            </button>
        </a>
        <div class="dropdown profile">
            <a class="d-flex dropdown-toggle " data-bs-toggle="dropdown">
                <div class="btn btn-outline-primary">
                    <i class="fa-regular fa-user"></i>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-3 card-dark border border-dark">
                <!-- <li><a class="dropdown-item" href="#">Perfil</a></li> -->
                <li><a class="dropdown-item" href="{{route('logout')}}">Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</nav>
{{-- Add this somewhere prominent, like at the top of your content section --}}
<div id="notification-alert" class="alert alert-warning d-none" role="alert">
    <strong>Notifications are blocked.</strong> To receive new booking alerts, please enable notifications for this site in your browser settings.
</div>