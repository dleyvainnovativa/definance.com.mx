@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.managed-cash-flow")}}'>
<input id="data_url_save" type="hidden" value='{{route("api.managed-cash-flow.save")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Flujo de Efectivo Administrado</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
</div>
<div class="fixed-bottom mb-4 text-end pe-4">
    <button id="save" class="btn btn-primary btn-lg shadow"><i class="fas fa-save"></i></button>
</div>
<div id="floating-header" class="position-sticky w-100 " style="z-index:998; top:60px !important;">
    <div class="mt-4">
        <div id="floating-content"></div>
    </div>
</div>
<div class="row g-4 mt-1">
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
    <div class="col-auto">
        <button id="refresh" class="btn btn-primary"><i class="fas fa-refresh"></i></button>
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
    <div class="col-12 text-dark">
        <div class="row g-4" id="cards-container">
            @include("components.loading.cards_body")

        </div>
    </div>
    <div class="col-12 text-dark" id="footer-card">
    </div>
</div>
<template id="tableTemplate" class="table_template">
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <span class="text-muted">(%code%) <span class="text-dark">%title%</span></span>
        <div class="text-end">
            <span class="badge text-bg-primary">%amount%</span>
            <span class="badge text-bg-secondary">%percent%%</span>
        </div>
    </li>
</template>
@vite(["resources/js/managed_cash_flow.js"])

@endsection