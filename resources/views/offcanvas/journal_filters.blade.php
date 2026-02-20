<div class="offcanvas offcanvas-end card-dark border border-dark" tabindex="-1" id="offcanvasFilter" aria-labelledby="offcanvasFilterLabel">
    <div class="offcanvas-header text-dark">
        <h5 class="offcanvas-title" id="offcanvasFilterLabel">Filtros</h5>
        <button type="button" class="btn ms-auto" data-bs-dismiss="offcanvas" aria-label="Cerrar">
            <i class="fas fa-xmark fa-lg text-dark"></i>
        </button>
    </div>
    <div class="offcanvas-body text-dark">

        <!-- Cta Cargo -->
        <div class="mb-4">
            <h6 class="text-uppercase text-muted">Cuenta Cargo</h6>
            <div class="d-flex gap-2 mb-2">
                <a class="" id="debit-select-all">Seleccionar Todo</a>
                <a class="" id="debit-clear">Limpiar</a>
            </div>
            <div id="filter-debit-accounts" class="filter-list"></div>

        </div>

        <hr>

        <!-- Cta Abono -->
        <div class="mb-4">
            <h6 class="text-uppercase text-muted">Cuenta Abono</h6>
            <div class="d-flex gap-2 mb-2">
                <a class="" id="credit-select-all">Seleccionar Todo</a>
                <a class="" id="credit-clear">Limpiar</a>
            </div>
            <div id="filter-credit-accounts" class="filter-list"></div>

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