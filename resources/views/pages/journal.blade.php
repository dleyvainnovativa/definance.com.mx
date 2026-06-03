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
        <div class="card h-100 border border-dark card-dark" onclick='editEntryMobile(%entry_id%)' style="cursor: pointer;">

            <!-- Header row: icon + type/date + badge -->
            <div class="card-header d-flex align-items-center justify-content-between py-4 px-3 border-bottom border-dark"
                style="background: transparent;">
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
                <div class="text-muted small border-start border-2 ps-2 lh-sm border-dark">
                    %description%
                </div>

                <!-- Amounts -->
                <div class="d-flex justify-content-between align-items-center pt-2 mt-auto border-top border-dark">
                    <div class="text-center flex-fill">
                        <div class="text-muted" style="font-size: 0.7rem;">Cargo</div>
                        <div class="fw-semibold %debit_class%">%debit%</div>
                    </div>
                    <div class="border-dark" style="width: 1px; height: 2rem;"></div>
                    <div class="text-center flex-fill">
                        <div class="text-muted" style="font-size: 0.7rem;">Abono</div>
                        <div class="fw-semibold %credit_class%">%credit%</div>
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