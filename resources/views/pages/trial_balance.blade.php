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
            <option value="total">Ajuste</option>

        </select>
    </div>
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="year" id="year-filter">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </div>
    <div class="col-auto text-start mt-auto">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="detailsCheckbox">
            <label class="form-check-label" for="detailsCheckbox">
                Mostrar detalles
            </label>
        </div>
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
                        <th data-field="opening" data-formatter="formatCurrency" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Saldo Inicial</th>
                        <th data-field="debit" data-formatter="formatCurrency" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Cargos</th>
                        <th data-field="credit" data-formatter="formatCurrency" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Abonos</th>
                        <th data-field="total" data-formatter="formatCurrency" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Saldo</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>

<template id="tableTemplate">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-dark border border-dark h-100">
            <div class="card-body p-4 text-dark">

                <!-- HEADER -->
                <div class="d-flex align-items-start justify-content-between mb-3">

                    <div class="d-flex align-items-center">
                        <div class="me-3 fs-4 text-primary">
                            %icon%
                        </div>
                        <div>
                            <div class="fw-semibold fs-6">
                                %account_name%
                            </div>
                            <div class="text-muted small">
                                %account_code% · %entry_type_label%
                            </div>
                        </div>
                    </div>

                    <span class="badge text-bg-light text-dark border">
                        %nature%
                    </span>

                </div>

                <!-- AMOUNTS -->
                <div class="small">

                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Saldo inicial</span>
                        <span class="fw-bold text-dark">%opening%</span>
                    </div>

                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Débitos</span>
                        <span class="text-success fw-bold">%debit%</span>
                    </div>

                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Créditos</span>
                        <span class="text-danger fw-bold">%credit%</span>
                    </div>

                </div>

                <!-- Divider (Soft) -->
                <div class="border-top my-3 opacity-25"></div>

                <!-- TOTAL -->
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold text-dark">
                        Saldo actual
                    </span>
                    <span class="fs-5 fw-bold %total_class%">
                        %total%
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>

@include("offcanvas.journal_filters")
@vite(["resources/js/trial-balance.js"])

@endsection