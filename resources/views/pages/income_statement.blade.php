@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.income-statement")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Estado de Resultados</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
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
</div>
<template id="tableTemplate" class="table_template">
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <span class="text-muted">(%code%) <span class="text-dark">%title%</span></span>
        <div class="text-end">
            <span class="badge text-bg-primary">%amount%</span>
            <span class="badge text-bg-secondary">%percent%%</span>
        </div>
    </li>
    <!-- <div class="col-12 col-md-12 col-lg-6 col-xl-4">
        <div class="text-bg-white border border-dark card card-dark h-100 position-relative">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">%title%</div>
                        <div class="text-muted small">%code%</div>
                    </div>
                    <div class="ms-3 fw-semibold">
                        <div class="col-12 text-dark text-end">
                            %amount%
                        </div>
                        <div class="col-12 text-muted text-end">
                            %percent%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</template>
@vite(["resources/js/income_statement.js"])

@endsection