@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.dashboard")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Dashboard</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
    <div>
        <button id="hidden_numbers" class="btn btn-outline-primary"><i class="fas fa-eye-slash"></i></button>
        <button id="refresh" class="btn btn-primary"><i class="fas fa-refresh"></i></button>
    </div>
</div>

<div class="row g-4 mt-1 blur-sensitive" id="dashboard-container">
    <!-- <div class="col-auto">
    </div> -->
    <!-- <div class="col-12 text-dark">
        <h4 class="fw-bold text-dark">Cuentas</h4>
        <div class="row g-4" id="cards-accounts">
            @include("components.loading.cards_header")
        </div>
    </div> -->
    <!-- <div class="col-12 text-dark">
        <div class="row g-2">
            <div class="col-8 my-auto">
                <h4 class="fw-bold text-dark">Cuentas de Efectivo y Tarjetas de Debito</h4>
            </div>
            <div class="col-4 me-auto my-auto">
                <h5 id="cards-debit-accounts-total" class="text-end text-success fw-bold">0.00</h5>
            </div>
        </div>
        <div class="row g-4" id="cards-debit-accounts">
            @include("components.loading.cards_header")
        </div>
    </div> -->
    <div class="col-12 text-dark">
        <div class="row g-2 mb-3">
            <div class="col-8 my-auto">
                <select id="select-debit-accounts" class="form-select card-dark border border-dark text-dark w-100">
                </select>
            </div>
            <div class="col-4 my-auto">
                <h5 id="cards-debit-accounts-total"
                    class="text-end text-success fw-bold">
                    0.00
                </h5>
            </div>
        </div>
        <div class="row g-4" id="cards-debit-accounts">
            @include("components.loading.cards_header")
        </div>
    </div>
    <div class="col-12 text-dark">
        <div class="row g-2 mb-3">
            <div class="col-8 my-auto">
                <select id="select-credit-accounts" class="form-select card-dark border border-dark text-dark w-100">
                </select>
            </div>
            <div class="col-4 my-auto">
                <h5 id="cards-credit-accounts-total"
                    class="text-end text-success fw-bold">
                    0.00
                </h5>
            </div>
        </div>
        <div class="row g-4" id="cards-credit-accounts">
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
                data-show-custom-view-button="true"
                data-show-custom-view="true"
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
                        <th class="" data-formatter="formatCurrency" data-field="debit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Cargos</th>
                        <th class="" data-formatter="formatCurrency" data-field="credit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Abonos</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

<template id="tableTemplate">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 border border-dark card-dark">

            <!-- Header row: icon + type/date + badge -->
            <div class="card-header d-flex align-items-center justify-content-between py-4 px-3"
                style="background: transparent; border-bottom: 1px solid var(--bs-border-color);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 p-2 %icon_bg%">
                        %icon%
                    </div>
                    <div>
                        <div class="fw-semibold small text-dark">%entry_type_label%</div>
                        <div class="text-muted" style="font-size: 0.7rem;">%entry_date%</div>
                    </div>
                </div>
                <span class="badge %badge_class% fw-normal" style="font-size: 0.7rem;">%amount_label%</span>
            </div>

            <div class="card-body p-4 d-flex flex-column gap-2">

                <!-- Accounts side by side -->
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-2 rounded-2 h-100 card-dark">
                            <div class="text-uppercase text-muted mb-1" style="font-size: 0.65rem; letter-spacing: 0.04em;">Cta Cargo</div>
                            <div class="fw-semibold small text-truncate text-dark">%debit_account_name%</div>
                            <div class="text-muted" style="font-size: 0.7rem;">%debit_account_code%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-2 h-100 card-dark">
                            <div class="text-uppercase text-muted mb-1" style="font-size: 0.65rem; letter-spacing: 0.04em;">Cta Abono</div>
                            <div class="fw-semibold small text-truncate text-dark">%credit_account_name%</div>
                            <div class="text-muted" style="font-size: 0.7rem;">%credit_account_code%</div>
                        </div>
                    </div>
                </div>

                <!-- Concept -->
                <div class="text-muted small border-start border-2 ps-2 lh-sm">
                    %description%
                </div>

                <!-- Amounts -->
                <div class="d-flex justify-content-between align-items-center pt-2 mt-auto"
                    style="border-top: 1px solid var(--bs-border-color);">
                    <div class="text-center flex-fill">
                        <div class="text-muted" style="font-size: 0.7rem;">Cargo</div>
                        <div class="fw-semibold %debit_class%">%debit%</div>
                    </div>
                    <div style="width: 1px; height: 2rem; background: var(--bs-border-color);"></div>
                    <div class="text-center flex-fill">
                        <div class="text-muted" style="font-size: 0.7rem;">Abono</div>
                        <div class="fw-semibold %credit_class%">%credit%</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

@vite(["resources/js/dashboard.js"])

@endsection