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
        <div class="card card-dark border border-dark">
            <div class="card-body p-4">
                <form id="entry-form" class="needs-validation" novalidate>

                    <div class="row g-4">

                        <!-- Entry date -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha del Movimiento</label>
                            <input type="date" name="entry_date" class="form-control" required>
                        </div>

                        <!-- Entry type (UI helper only) -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Movimiento</label>
                            <select id="entry_type" class="form-select" required>
                                <option value="null" disabled selected>Seleccione...</option>
                                <option value="INGRESO">INGRESO</option>
                                <option value="EGRESO">EGRESO</option>
                                <option value="TRASPASO">TRASPASO</option>
                                <option value="SALDO INICIAL">SALDO INICIAL</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Monto</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referencia</label>
                            <input type="text" name="reference" class="form-control" required>
                        </div>

                        <!-- Debit -->
                        <div class="col-md-6">
                            <label class="form-label">Cuenta Debe</label>
                            <select name="debit_account_id" id="debit_account_id" class="form-select" required></select>
                        </div>

                        <!-- Credit -->
                        <div class="col-md-6">
                            <label class="form-label">Cuenta Haber</label>
                            <select name="credit_account_id" id="credit_account_id" class="form-select" required></select>
                        </div>

                        <!-- Concept -->
                        <div class="col-12">
                            <label class="form-label">Concepto</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@vite(["resources/js/entry.js"])

@endsection