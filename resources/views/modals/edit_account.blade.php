<div class="modal fade border border-dark" id="accountEditModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form id="account-edit-form" class="modal-content border border-dark needs-validation" novalidate>
            @csrf
            <div class="modal-header  card-dark border-0 px-4 pt-4">
                <div>
                    <h5 class="text-dark display" id="messageBottomSheetLabel">Editar Cuenta</h5>
                    <p class="text-muted mb-0 pb-0">
                        Añadir una nueva cuenta contable</p>
                </div>
                <button type="button" class="btn ms-auto" data-bs-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-xmark fa-lg text-dark"></i>
                </button>
            </div>
            <div class="modal-body card-dark px-4 pb-4 text-dark">
                <div class="row g-4">
                    <input type="hidden" name="account_id" id="account_edit_id">
                    <div class="col-12">
                        <label>Name</label>
                        <input type="text" name="account_name" id="account_edit_name" class="form-control card-dark text-dark border border-dark" required>
                    </div>
                </div>

            </div>

            <div class="modal-footer card-dark border-0">
                <a class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</a>
                <button type="submit" class="btn btn-primary">Editar cuenta</button>
            </div>
        </form>
    </div>
</div>