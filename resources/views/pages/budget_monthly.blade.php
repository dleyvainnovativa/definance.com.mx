@extends('main')

@section('content')

<input id="data_url" type="hidden" value='{{route("api.budget-monthly")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Presupuesto Mensual</h3>
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
        <div class="row g-4" data-masonry='{"percentPosition": true }' id="cards-container">
            @include("components.loading.cards_body")
        </div>
    </div>
</div>
<template id="tableTemplate" class="table_template">
    <li class="list-group-item py-3 ">

        <div class="d-flex justify-content-between align-items-start">

            <!-- LEFT -->
            <div>
                <div class="fw-semibold fs-6">
                    %title%
                </div>
                <div class="text-muted small">
                    Código: %code%
                </div>
            </div>

            <!-- RIGHT -->
            <div class="text-end">

                <!-- Actual -->
                <div class="fw-bold fs-5">
                    %amount%
                </div>

                <!-- Budget -->
                <div class="small text-muted">
                    Presupuesto: %amount_budget%
                </div>

                <!-- Difference -->
                <div class="small mt-1">
                    <span class="badge %difference_class%">
                        %amount_difference%
                    </span>
                </div>

                <!-- Percent -->
                <div class="small text-muted mt-1">
                    %percent%% del total
                </div>

            </div>

        </div>

        <!-- Optional Progress -->
        <div class="progress mt-3" style="height:6px;">
            <div class="progress-bar bg-success"
                role="progressbar"
                %percent_bar%>
            </div>
        </div>
    </li>
</template>
@vite(["resources/js/budget_monthly.js"])

@endsection