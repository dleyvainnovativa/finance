@extends('main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Balance de Comprobación</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>

</div>
<div class="row g-4 mt-1">
    <div class="col-auto ms-auto text-end">
        <select class="form-select" name="month" id="month-filter">
            <option value="1" selected>Enero</option>
            <option value="2">Febrero</option>
            <option value="3">Marzo</option>
            <option value="4">Abril</option>
        </select>
    </div>
    <div class="col-auto text-end">
        <select class="form-select" name="year" id="year-filter">
            <option value="2026" selected>2026</option>
            <option value="2025">2025</option>
        </select>
    </div>
    <div class="col-12">
        <div class="table-responsive">
            <table id="trial-balance-table"
                class="table text-bg-dark card-dark border-dark"
                data-url="{{ route('api.trial-balance') }}"
                data-pagination="true"
                data-side-pagination="server"
                data-page-size="10"
                data-search="true"
                data-filter-control="true"
                data-filter-show-clear="true"
                data-show-refresh="true"
                data-show-footer="true"
                data-response-handler="responseHandler"
                data-ajax="ajaxRequest">
                <thead>
                    <tr>
                        <th data-field="account_code" data-sortable="true">CUENTA CONTABLE</th>
                        <th data-field="nature">NATURALEZA</th>
                        <th data-field="type">TIPO</th>
                        <th data-field="account_name">NOMBRE CUENTA</th>
                        <th data-field="category">CATEGORÍA</th>
                        <th data-field="opening_balance" data-align="right">SALDO INICIAL</th>
                        <th data-field="debit" data-align="right">CARGO</th>
                        <th data-field="credit" data-align="right">ABONO</th>
                        <th data-field="final_balance" data-align="right">SALDO FINAL</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@vite(['resources/js/trial-balance.js'])

@endsection