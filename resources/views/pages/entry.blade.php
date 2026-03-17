@extends('main')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Registrar Nuevo Movimiento</h3>
        <p class="text-muted pb-0 mb-0">Añadir un nuevo asiento contable al diario</p>
    </div>
</div>
<div class="row g-4 mt-1">
    <div class="col-12">
        <form id="entry-form" class="needs-validation" novalidate>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Fecha del Movimiento</label>
                    <input type="date" name="entry_date" class="form-control card-dark border border-dark text-dark" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select id="entry_type" class="form-select card-dark border border-dark text-dark" required>
                        <option value="null" disabled selected>Seleccione...</option>
                        <option value="income">INGRESO</option>
                        <option value="expense">EGRESO</option>
                        <option value="transfer">TRASPASO</option>
                        <option value="asset_acquisition">ADQUISICIÓN ACT. FIJO</option>
                        <option value="income">SALDO INICIAL CUENTA DEUDORA</option>
                        <option value="expense">SALDO INICIAL CUENTA ACREEDORA</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Monto</label>
                    <input type="number" name="amount" step="0.01" class="form-control card-dark border border-dark text-dark" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Referencia</label>
                    <input type="text" name="reference" class="form-control card-dark border border-dark text-dark" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Forma de Pago</label>
                    <select name="debit_account_id" id="debit_account_id" class="form-select" required disabled></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cuenta Abono</label>
                    <select name="credit_account_id" id="credit_account_id" class="form-select" disabled></select>
                </div>
                <div class="col-12">
                    <label class="form-label">Concepto</label>
                    <textarea name="description" class="form-control card-dark border border-dark text-dark" required></textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                </div>
            </div>
        </form>
    </div>
</div>
@vite(["resources/js/entry.js"])
@endsection