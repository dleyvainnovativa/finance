@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Pólizas Contables</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>

</div>
<div class="row g-1 mt-1">
    <div class="col-auto">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter" aria-controls="offcanvasFilter">Filtros</button>

    </div>
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="month" id="month-filter">
            <option value="1" selected>Enero</option>
            <option value="2">Febrero</option>
            <option value="3">Marzo</option>
            <option value="4">Abril</option>
        </select>
    </div>
    <div class="col-auto text-start">
        <select class="form-select card-dark border border-dark text-dark" name="year" id="year-filter">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </div>

    <div class="col-12">
        <div class="table-responsive">
            <table id="journal-table"
                class="table text-bg-dark card-dark border-dark"
                data-url="{{ route('api.voucher') }}"
                data-pagination="true"
                data-side-pagination="server"
                data-page-size="10"
                data-search="true"
                data-search-align="left"
                data-buttons-align="left"
                data-filter-control="true"
                data-filter-show-clear="true"
                data-show-refresh="true"
                data-show-footer="true"
                data-response-handler="responseHandler"
                data-ajax="ajaxRequest">
                <thead>
                    <tr>
                        <th data-field="entry_date" data-footer-formatter="footerNullText" data-sortable="true">Fecha</th>
                        <th data-field="entry_type_label" data-footer-formatter="footerNullText" data-sortable="true">Tipo</th>
                        <th data-field="debit_account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Cargo</th>
                        <th data-field="debit_account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th data-field="credit_account_name" data-footer-formatter="footerNullText" data-sortable="true">Cta Abono</th>
                        <th data-field="credit_account_code" data-footer-formatter="footerNullText" data-sortable="true">ID Contable</th>
                        <th data-field="description" data-footer-formatter="footerLabel" data-falign="left">Concepto</th>
                        <th data-field="debit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Cargos</th>
                        <th data-field="credit" data-footer-formatter="footerSum" data-falign="left" data-sortable="true">Abonos</th>
                    </tr>
                </thead>
            </table>

        </div>
    </div>
</div>
@include("offcanvas.journal_filters")
@vite(["resources/js/voucher.js"])

@endsection