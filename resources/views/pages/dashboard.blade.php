@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.dashboard")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Dashboard</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
    <button id="refresh" class="btn btn-primary"><i class="fas fa-refresh"></i></button>
</div>

<div class="row g-4 mt-1">
    <!-- <div class="col-auto">
    </div> -->
    <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Cuentas</h4>
        <div class="row g-4" id="cards-accounts">
            @include("components.loading.cards_header")
        </div>
    </div>
    <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Estado de Posición Financiera</h4>
        <div class="row g-4" id="cards-header-balance">
            @include("components.loading.cards_header")
        </div>
    </div>
    <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Estado de Resultados del Mes</h4>
        <div class="row g-4" id="cards-header-income_month">
            @include("components.loading.cards_header")
        </div>
    </div>
    <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Estado de Resultados del Año</h4>
        <div class="row g-4" id="cards-header-income_year">
            @include("components.loading.cards_header")
        </div>
    </div>
    <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Movimientos Recientes</h4>
        <div class="table-responsive">
            <table id="journal-table"
                class="table text-bg-dark card-dark border-dark"
                data-toggle="table"
                data-page-size="10"
                data-search-align="left"
                data-buttons-align="left"
                data-custom-view="customViewFormatter">
                <thead>
                    <tr>
                        <th class="" data-field="entry_date" data-footer-formatter="footerNullText" data-sortable="true">Fecha</th>
                        <th class="" data-field="entry_type_label" data-footer-formatter="footerNullText" data-sortable="true">Tipo</th>
                        <th class="" data-field="debit_account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Cargo</th>
                        <th class="" data-field="debit_account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th class="" data-field="credit_account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Abono</th>
                        <th class="" data-field="credit_account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th class="" data-field="description" data-footer-formatter="footerLabel" data-falign="left">Concepto</th>
                        <th class="" data-field="debit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Cargos</th>
                        <th class="" data-field="credit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Abonos</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>
@vite(["resources/js/dashboard.js"])

@endsection