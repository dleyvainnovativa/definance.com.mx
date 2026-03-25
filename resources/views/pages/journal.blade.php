@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Estado de Cuenta</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>

</div>
<div class="row g-2 mt-1">
    <div class="col-auto">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter" aria-controls="offcanvasFilter">Filtros</button>

    </div>
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
            <option value="total">Cierre</option>

        </select>
    </div>
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="year" id="year-filter">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </div>
    <div class="col-auto text-start">
        <button class="btn btn-danger" onclick="removeMultipleJournals()">
            <i class="fas fa-trash"></i>
        </button>
    </div>


    <div class="col-12">
        <div class="table-responsive">
            <table id="journal-table"
                class="table text-bg-dark card-dark border-dark"
                data-url="{{ route('api.journal') }}"
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
                data-sticky-header="true"
                data-sticky-header-offset-y="60"
                data-show-custom-view-button="true"
                data-ajax="ajaxRequest">
                <thead>
                    <tr>
                        <th data-checkbox="true" data-field="checkbox"></th>
                        <th class="" data-field="entry_date" data-footer-formatter="footerNullText" data-sortable="true">Fecha</th>
                        <th class="" data-field="entry_type_label" data-footer-formatter="footerNullText" data-sortable="true">Tipo</th>
                        <th class="" data-visible=false data-field="debit_account_id" data-footer-formatter="footerNullText" data-sortable="true">ID</th>
                        <th class="" data-field="debit_account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Cargo</th>
                        <th class="" data-field="debit_account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th class="" data-visible=false data-field="credit_account_id" data-footer-formatter="footerNullText" data-sortable="true">ID</th>
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


<template id="tableTemplate" class="">
    <div class="col-12 col-md-12 col-lg-6 col-xl-4">
        <div class="text-bg-white border border-dark card card-dark h-100 position-relative">
            <div class="card-body p-4 text-dark">
                <div class="d-flex align-items-center justify-content-between">

                    <!-- Leading icon -->
                    <div class="me-3 text-primary fs-4">
                        %icon%
                    </div>

                    <!-- Title + subtitle -->
                    <div class="flex-grow-1">
                        <div class="fw-semibold">%title%</div>
                        <div class="text-muted small">%subtitle%</div>
                    </div>

                    <!-- Trailing amount -->
                    <div class="ms-3 fw-semibold %amount_class%">
                        %amount%
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@include("offcanvas.journal_filters")
@include("modals.edit_journal")
@vite(["resources/js/journal.js"])

@endsection