<div class="modal fade border border-dark" id="accountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form id="account-form" class="modal-content border border-dark needs-validation" novalidate>
            @csrf
            <div class="modal-header  card-dark border-0 px-4 pt-4">
                <div>
                    <h5 class="text-dark display" id="messageBottomSheetLabel">Agregar Nueva Cuenta</h5>
                    <p class="text-muted mb-0 pb-0">
                        Añadir una nueva cuenta contable</p>
                </div>
                <button type="button" class="btn ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-xmark fa-lg text-dark"></i>
                </button>
            </div>
            <div class="modal-body card-dark px-4 pb-4 text-dark">
                <div class="row g-4">

                    <div class="col-12">
                        <label for="parent_id" class="form-label">Categorías
                            <span class="badge text-bg-primary" id="badge_root"></span>
                        </label>
                        <select id="parent_id" name="parent_id" class="form-select card-dark border border-dark" required>

                        </select>
                    </div>
                    <div class="col-12">
                        <label>Code</label>
                        <div class="input-group">
                            <span class="input-group-text card-dark text-dark border border-dark" id="code_prefix">0.</span>
                            <input type="text" id="code" name="code" disabled class="form-control card-dark text-dark border border-dark" required>
                        </div>
                    </div>
                    <input type="hidden" name="account_code" id="account_code" required>
                    <input type="hidden" name="account_type" id="account_type" required>
                    <input type="hidden" name="account_parent_id" id="account_parent_id" required>


                    <div class="col-12">
                        <label>Name</label>
                        <input type="text" name="name" id="account_name" class="form-control card-dark text-dark border border-dark" required>
                    </div>
                    <div class="col-auto text-end ms-auto">
                        <a class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</a>
                    </div>
                    <div class="col-auto text-end">
                        <button type="submit" class="btn btn-outline-primary">Agregar cuenta</button>
                    </div>
                </div>

            </div>

            <!-- <div class="modal-footer card-dark border-0">
                <a class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</a>
                <button type="submit" class="btn btn-outline-primary">Agregar cuenta</button>
            </div> -->
        </form>
    </div>
</div>