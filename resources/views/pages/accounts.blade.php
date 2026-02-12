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
        <div id="accountsGrid"></div>

    </div>
</div>
@vite(["resources/js/accounts.js"])
@include("modals.add_account")

@endsection