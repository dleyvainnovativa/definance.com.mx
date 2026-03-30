<div class="modal fade border border-dark" id="journalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <form id="journal-form" class="modal-content border border-dark needs-validation" novalidate>
            @csrf
            <div class="modal-header  card-dark border-0 px-4 pt-4">
                <div>
                    <h5 class="text-dark display" id="messageBottomSheetLabel">Editar Movimiento</h5>
                    <p class="text-muted mb-0 pb-0">
                        Editar los datos de movimiento</p>
                </div>
                <button type="button" class="btn ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-xmark fa-lg text-dark"></i>
                </button>
            </div>
            <div class="modal-body card-dark px-4 pb-4 text-dark">
                <div class="row g-4">
                    <input type="hidden" name="journal_entry_id" id="journal_entry_id">
                    <!-- Entry date -->
                    <div class="col-md-6">
                        <label class="form-label">Forma de Pago</label>
                        <select name="debit_account_id" id="journal_debit_account_id" class="form-select" required></select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cuenta Abono</label>
                        <select name="credit_account_id" id="journal_credit_account_id" class="form-select"></select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fecha del Movimiento</label>
                        <input type="date" name="entry_date" id="journal_entry_date" class="form-control card-dark border border-dark text-dark" required="">
                    </div>

                    <!-- Entry type (UI helper only) -->
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Movimiento</label>
                        <select id="journal_entry_type" class="form-select card-dark border border-dark text-dark" required>
                            <option value="null" disabled selected>Seleccione...</option>
                            <option value="income">INGRESO</option>
                            <option value="expense">EGRESO</option>
                            <option value="transfer">TRASPASO</option>
                            <option value="asset_acquisition">ADQUISICIÓN ACT. FIJO</option>
                            <option value="income">SALDO INICIAL CUENTA DEUDORA</option>
                            <option value="expense">SALDO INICIAL CUENTA ACREEDORA</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="col-md-12">
                        <label class="form-label">Monto</label>
                        <input type="number" id="journal_entry_amount" name="amount" step="0.01" class="form-control card-dark border border-dark text-dark" required="">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Concepto</label>
                        <input type="text" id="journal_entry_concept" name="description" class="form-control card-dark border border-dark text-dark" required="">
                    </div>
                </div>

            </div>

            <div class="modal-footer card-dark border-0 px-4">
                <a class="btn btn-outline-danger me-auto ms-0" data-bs-dismiss="modal" onclick="removeJournal()">Borrar</a>
                <a class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</a>
                <button type="submit" class="btn btn-primary me-0">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>