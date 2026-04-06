@extends('main')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Arqueo</h3>
        <p class="text-muted pb-0 mb-0">Realiza tu arqueo insertando cuanto dinero tienes en efectivo</p>
    </div>
</div>
<div class="row g-4 mt-1">
    <input id="data_url" type="hidden" value='{{route("api.cash-count")}}'>
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
    <div class="col-12">
        <div class="card shadow-sm card-dark">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="">
                            <tr>
                                <th>Cantidad</th>
                                <th>Denominación</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="cash-table">

                            @php
                            $denominations = [1000,500,200,100,50,20,10,5,2,1,0.5];
                            @endphp

                            @foreach ($denominations as $value)
                            <tr>
                                <td class="">
                                    <input type="number" min="0" class="form-control text-center qty card-dark text-dark border border-dark"
                                        data-value="{{ $value }}" value="0">
                                </td>
                                <td>${{ number_format($value, 2) }}</td>
                                <td class="row-total text-end">$0.00</td>
                            </tr>
                            @endforeach

                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Saldo</th>
                                <th class="text-end" id="saldo">$0.00</th>
                            </tr>

                            <tr>
                                <th colspan="2" class="text-end"><button data-bs-toggle="modal" data-bs-target="#auxModal" class="btn btn-primary btn-sm"><i class="fas fa-gear"></i></button> S. en Aux</th>
                                <th>
                                    <input type="text" id="aux" class="form-control text-end card-dark text-dark border border-dark" value="0">
                                </th>
                            </tr>
                            <tr>
                                <th colspan="2" class="text-end">Diferencia</th>
                                <th id="difference" class="fw-bold text-end">$0.00</th>
                            </tr>
                        </tfoot>

                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@include("modals.settings_cash_count")
@vite(["resources/js/cash_count.js"])
@endsection