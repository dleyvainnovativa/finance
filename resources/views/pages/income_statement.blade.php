@extends('main')

@section('content')
<input id="data_url" type="hidden" value='{{route("api.income-statement")}}'>
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Estado de Resultados</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>
</div>
<div class="row g-4 mt-1">
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
        <div class="row g-4" id="cards-header"></div>
    </div>
    <div class="col-12">
        <div class="row g-4" id="cards-container"></div>
    </div>
</div>
@vite(["resources/js/income_statement.js"])

@endsection