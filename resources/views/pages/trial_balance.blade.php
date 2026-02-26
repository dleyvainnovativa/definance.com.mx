@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Balance de Comprobación</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>

</div>
<div class="row g-4 mt-1">
    <!-- <div class="col-auto">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter" aria-controls="offcanvasFilter">Filtros</button>

    </div> -->
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="month" id="month-filter">
            <option value="1" selected>Enero</option>
            <option value="2">Febrero</option>
            <option value="3">Marzo</option>
            <option value="4">Abril</option>
            <option value="5">Mayo</option>
            <option value="6">Junio</option>
            <option value="7">Julio</option>
            <option value="8">Agosto</option>
            <option value="9">Septiembre</option>
            <option value="10">Octubre</option>
            <option value="11">Noviembre</option>
            <option value="12">Diciembre</option>

        </select>
    </div>
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="year" id="year-filter">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </div>

    <div class="col-12 text-dark">
        <div class="row g-4" id="cards-header">
            @include("components.loading.cards_header")
        </div>
    </div>

    <div class="col-12">
        <div class="table-responsive">
            <table id="journal-table"
                class="table text-bg-dark card-dark border-dark"
                data-url="{{ route('api.trial-balance') }}"
                data-pagination="true"
                data-side-pagination="server"
                data-page-size="10"
                data-search="true"
                data-search-align="left"
                data-buttons-align="left"
                data-filter-control="true"
                data-filter-show-clear="true"
                data-show-refresh="true"
                data-show-footer="true"
                data-response-handler="responseHandler"
                data-show-custom-view="true"
                data-custom-view="customViewFormatter"
                data-show-custom-view-button="true"
                data-sticky-header="true"
                data-sticky-header-offset-y="60"
                data-ajax="ajaxRequest">
                <thead>
                    <tr>
                        <th data-field="entry_type_label" data-footer-formatter="footerNullText" data-sortable="true">Tipo</th>
                        <th data-visible="false" data-field="entry_type" data-footer-formatter="footerNullText" data-sortable="true">Tipo</th>
                        <th data-field="nature" data-footer-formatter="footerNullText" data-sortable="true">Naturaleza</th>
                        <th data-field="account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Cargo</th>
                        <th data-field="account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th data-field="opening" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Saldo Inicial</th>
                        <th data-field="debit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Cargos</th>
                        <th data-field="credit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Abonos</th>
                        <th data-field="total" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Saldo</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

<template id="tableTemplate">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-dark border border-dark shadow-sm h-100">
            <div class="card-body p-3">

                <!-- Header -->
                <div class="d-flex align-items-center mb-2">
                    <div class="me-2 fs-4">
                        %icon%
                    </div>
                    <div>
                        <div class="fw-semibold text-dark">%account_name%</div>
                        <div class="text-muted small">
                            %account_code% · %entry_type_label% · %nature%
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                <!-- Amounts -->
                <div class="row small">
                    <div class="col-6 text-muted">Saldo inicial</div>
                    <div class="col-6 text-end text-dark">%opening%</div>

                    <div class="col-6 text-muted">Débitos</div>
                    <div class="col-6 text-end text-success">%debit%</div>

                    <div class="col-6 text-muted">Créditos</div>
                    <div class="col-6 text-end text-danger">%credit%</div>
                </div>

                <hr class="my-2">

                <!-- Total -->
                <div class="d-flex justify-content-between fw-semibold">
                    <span class="text-dark">Saldo</span>
                    <span class="%total_class%">%total%</span>
                </div>

            </div>
        </div>
    </div>
</template>

@include("offcanvas.journal_filters")
@vite(["resources/js/trial-balance.js"])

@endsection