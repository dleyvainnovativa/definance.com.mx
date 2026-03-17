<div class="offcanvas offcanvas-end card-dark border border-dark" tabindex="-1" id="offcanvasFilter" aria-labelledby="offcanvasFilterLabel">

    <div class="offcanvas-header text-dark">
        <h5 class="offcanvas-title" id="offcanvasFilterLabel">Filtros</h5>
        <button type="button" class="btn ms-auto" data-bs-dismiss="offcanvas" aria-label="Cerrar">
            <i class="fas fa-xmark fa-lg text-dark"></i>
        </button>
    </div>

    <div class="offcanvas-body text-dark">

        <div class="accordion" id="filtersAccordion">

            <div class="accordion-item bg-transparent border-dark">
                <h2 class="accordion-header" id="headingType">
                    <button class="accordion-button collapsed bg-transparent text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTypes">
                        Tipos de Movimiento
                    </button>
                </h2>

                <div id="collapseTypes" class="accordion-collapse collapse show" data-bs-parent="#filtersAccordion">
                    <div class="accordion-body">

                        <div class="d-flex gap-2 mb-2">
                            <a class="" id="type-select-all">Seleccionar Todo</a>
                            <a class="" id="type-clear">Limpiar</a>
                        </div>

                        <div id="filter-type-entries" class="filter-list text-dark">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="opening_balance" data-filter="entry_type">
                                <label class="form-check-label">Saldo Inicial Cuenta Deudora</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="opening_balance_credit" data-filter="entry_type">
                                <label class="form-check-label">Saldo Inicial Cuenta Acreedora</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="income" data-filter="entry_type" checked>
                                <label class="form-check-label">Ingreso</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="expense" data-filter="entry_type" checked>
                                <label class="form-check-label">Egreso</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="transfer" data-filter="entry_type" checked>
                                <label class="form-check-label">Transferencia</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="asset_acquisition" data-filter="entry_type" checked>
                                <label class="form-check-label">Adquisición de Activo</label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- Cuenta Cargo -->
            <div class="accordion-item bg-transparent border-dark">
                <h2 class="accordion-header" id="headingDebit">
                    <button class="accordion-button collapsed bg-transparent text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDebit">
                        Cuenta Cargo
                    </button>
                </h2>

                <div id="collapseDebit" class="accordion-collapse collapse " data-bs-parent="#filtersAccordion">
                    <div class="accordion-body">

                        <div class="d-flex gap-2 mb-2">
                            <a class="" id="debit-select-all">Seleccionar Todo</a>
                            <a class="" id="debit-clear">Limpiar</a>

                        </div>

                        <div id="filter-debit-accounts" class="filter-list text-dark"></div>

                    </div>
                </div>
            </div>

            <!-- Cuenta Abono -->
            <div class="accordion-item bg-transparent border-dark">
                <h2 class="accordion-header" id="headingCredit">
                    <button class="accordion-button collapsed bg-transparent text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCredit">
                        Cuenta Abono
                    </button>
                </h2>

                <div id="collapseCredit" class="accordion-collapse collapse" data-bs-parent="#filtersAccordion">
                    <div class="accordion-body">

                        <div class="d-flex gap-2 mb-2">
                            <a class="" id="credit-select-all">Seleccionar Todo</a>
                            <a class="" id="credit-clear">Limpiar</a>
                        </div>

                        <div id="filter-credit-accounts" class="filter-list text-dark"></div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="offcanvas-footer p-3 border-top border-dark">
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary w-50" id="filters-reset">
                Limpiar todo
            </button>
            <button class="btn btn-success w-50" id="filters-apply">
                Aplicar filtros
            </button>
        </div>
    </div>

</div>