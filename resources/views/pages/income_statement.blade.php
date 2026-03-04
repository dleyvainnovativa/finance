@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.income-statement")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Estado de Resultados</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
</div>
<div class="row g-2 mt-1">
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
            <option value="total">Total</option>
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

    <div class="col-auto">
        <ul class="nav nav-pills" role="tablist">

            <li class="nav-item pe-2" role="presentation">
                <button
                    class="btn active btn-outline-primary"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-table"
                    type="button"
                    role="tab">
                    <i class="fas fa-table me-2"></i>Tablas
                </button>
            </li>

            <li class="nav-item pe-2" role="presentation">
                <button
                    class="btn btn-outline-primary"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-chart"
                    type="button"
                    role="tab">
                    <i class="fas fa-chart-line me-2"></i>Gráficas
                </button>
            </li>

        </ul>
    </div>
</div>

<div class="tab-content mt-3">
    <!-- 🟢 TABLE TAB -->
    <div class="tab-pane fade show active" id="tab-table">
        <div class="row g-4 mt-1">
            <div class="col-12 text-dark">
                <div class="row g-4" id="cards-header">
                    @include("components.loading.cards_header")
                </div>
            </div>
            <div class="col-12 text-dark">
                <div class="row g-4" id="cards-container">
                    @include("components.loading.cards_body")
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="tab-chart">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <canvas id="financialChart" height=""></canvas>
            </div>
        </div>
    </div>
</div>

<template id="tableTemplate" class="table_template">
    <li class="list-group-item py-3">
        <div class="d-flex justify-content-between align-items-center">

            <!-- Left -->
            <div>
                <div class="fw-semibold fs-6">
                    %title%
                </div>
                <div class="text-muted small">
                    Código: %code%
                </div>
            </div>

            <!-- Right -->
            <div class="text-end">
                <div class="fw-bold fs-5">
                    %amount%
                </div>

                <div class="d-flex justify-content-end gap-2 mt-1">
                    <span class="badge bg-primary-subtle text-primary">
                        %percent%%
                    </span>
                </div>
            </div>

        </div>
    </li>
</template>
@vite(["resources/js/income_statement.js"])

@endsection