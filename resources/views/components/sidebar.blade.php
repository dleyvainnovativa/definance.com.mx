<div id="sidebar-wrapper" class="border border-dark card-dark sidebar safe-area">
    <div class="sidebar-heading text-primary text-center">
        <img class="text-center" src="{{asset('img/logo2.png')}}" width="70" alt="">
    </div>
    <div class="d-flex flex-column vh-100 pb-5">
        <!-- <div class="list-group list-group-flush overflow-auto pb-5 flex-shrink-0" style="height:-webkit-fill-available;"> -->
        <div class="list-group list-group-flush overflow-auto flex-grow-1 pb-5">

            <div class="sidebar-section-label text-muted fw-bold">Overview</div>

            <a href="{{ route('home') }}" class="list-group-item list-group-item-action {{ request()->routeIs('home') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-chart-line text-primary start-icon"></i>
                    Dashboard
                </small>
            </a>

            <a href="{{ route('accounts') }}" class="list-group-item list-group-item-action {{ request()->routeIs('accounts') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-sitemap text-primary start-icon"></i>
                    Cuentas
                </small>
            </a>
            <a href="{{ route('import') }}" class="list-group-item list-group-item-action {{ request()->routeIs('import') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-file-import text-primary start-icon"></i>
                    Importar
                </small>
            </a>

            <div class="sidebar-section-label text-muted fw-bold">Procesos</div>

            <a href="{{ route('journal') }}" class="list-group-item list-group-item-action {{ request()->routeIs('journal') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-right-left text-primary start-icon"></i>
                    Estado de Cuenta
                </small>
            </a>

            <div class="sidebar-section-label text-muted fw-bold">Reportes</div>

            <a href="{{ route('voucher') }}" class="list-group-item list-group-item-action {{ request()->routeIs('voucher') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-file-invoice text-primary start-icon"></i>
                    Pólizas Contables
                </small>
            </a>
            <a href="{{ route('trial_balance') }}" class="list-group-item list-group-item-action {{ request()->routeIs('trial_balance') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-scale-balanced text-primary start-icon"></i>
                    Balance de Comprobación
                </small>
            </a>

            <a href="{{ route('income_statement') }}" class="list-group-item list-group-item-action {{ request()->routeIs('income_statement') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-file-lines text-primary start-icon"></i>
                    Estado de Resultados
                </small>
            </a>

            <a href="{{ route('cash_flow') }}" class="list-group-item list-group-item-action {{ request()->routeIs('cash_flow') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-money-bill-wave text-primary start-icon"></i>
                    Flujo de Efectivo
                </small>
            </a>


            <a href="{{ route('balance_sheet') }}" class="list-group-item list-group-item-action {{ request()->routeIs('balance_sheet') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-building-columns text-primary start-icon"></i>
                    Estado de Posición
                </small>
            </a>
            <a href="{{ route('average') }}" class="list-group-item list-group-item-action {{ request()->routeIs('average') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-calculator text-primary start-icon"></i>
                    Promedios
                </small>
            </a>

            <div class="sidebar-section-label text-muted fw-bold">Manual</div>

            <a href="{{ route('managed_cash_flow') }}" class="list-group-item list-group-item-action {{ request()->routeIs('managed_cash_flow') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-money-bill-trend-up text-primary start-icon"></i>
                    FEA
                </small>
            </a>

            <a href="{{ route('budget') }}" class="list-group-item list-group-item-action {{ request()->routeIs('budget') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-calendar-plus text-primary start-icon"></i>
                    Presupuesto
                </small>
            </a>
            <a href="{{ route('budget_monthly') }}" class="list-group-item list-group-item-action {{ request()->routeIs('budget_monthly') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-calendar-check text-primary start-icon"></i>
                    Presupuesto Mensual
                </small>
            </a>
            <a href="{{ route('cash_count') }}" class="list-group-item list-group-item-action {{ request()->routeIs('cash_count') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-money-check-alt text-primary start-icon"></i>
                    Arqueo
                </small>
            </a>
            <!-- <a href="{{ route('projections') }}" class="list-group-item list-group-item-action {{ request()->routeIs('projections') ? 'active' : '' }}">
                <small class="text-dark">
                    <i class="fa-solid fa-chart-pie text-primary start-icon"></i>
                    Proyecciones
                </small>
            </a> -->

        </div>

        <div class="sidebar-bottom-btn sticky-bottom mt-auto card-dark">
            <div class="safe-area mt-auto border-top border-dark">
                <div class="row g-2 p-3">
                    <!-- <div class="col-12">
                        <button class="btn btn-outline-light w-100 text-dark border border-dark">
                            <i class="fa-solid fa-gear me-2"></i> Settings
                        </button>
                    </div> -->
                    <div class="col-12">
                        <a href="{{route('logout')}}" class="btn btn-primary w-100">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>