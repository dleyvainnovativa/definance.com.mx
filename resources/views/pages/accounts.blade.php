@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Cuentas Contables</h3>
        <p class="text-muted pb-0 mb-0">Manage your accounts</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#accountModal">
        <span class="d-block d-md-none">
            +
        </span>
        <span class="d-none d-md-block">
            + Agregar
        </span>
    </button>
</div>
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="table-responsive">
            <table id="journal-table"
                class="table text-bg-dark card-dark border-dark"
                data-url="{{ route('api.accounts') }}"
                data-pagination="true"
                data-side-pagination="server"
                data-page-size="10"
                data-search="true"
                data-search-align="left"
                data-buttons-align="left"
                data-filter-control="true"
                data-filter-show-clear="true"
                data-show-refresh="true"
                data-response-handler="responseHandler"
                data-show-custom-view="true"
                data-custom-view="customViewFormatter"
                data-show-custom-view-button="true"
                data-ajax="ajaxRequest">
                <thead>
                    <tr>
                        <th data-field="id" data-formatter="actionsFormatter" data-sortable="true">Acciones</th>
                        <th data-field="code" data-sortable="true">Código</th>
                        <th data-field="name" data-sortable="true">Cuenta</th>
                        <th data-field="nature_label" data-sortable="true">Naturaleza</th>
                        <th data-field="type" data-visible="false" data-sortable="true">Tipo</th>
                        <th data-field="type_label" data-sortable="true">Tipo</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>


<template id="tableTemplate" class="">
    <div class="col-12 col-md-12 col-lg-6 col-xl-4">
        <div class="text-bg-white border border-dark card card-dark h-100 position-relative">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="me-2 fs-4">
                            %icon%
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">%account_name%</div>
                            <div class="text-muted small">
                                %account_code% · %type_label% · %nature_label%
                            </div>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button %is_editable%
                            class="btn btn-outline-primary"
                            %edit%
                            title="Editar cuenta">
                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <button %is_deletable%
                            class="btn btn-outline-danger"
                            %remove%
                            title="Eliminar cuenta">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@vite(["resources/js/accounts.js"])
@include("modals.add_account")
@include("modals.edit_account")

@endsection