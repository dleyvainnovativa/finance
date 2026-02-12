@extends('main')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

<div class="container">
    <h4 class="mb-4">Importar Movimientos (Excel)</h4>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card card-dark border border-dark">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Archivos Excel (.xlsx)</label>

                        <div id="dropzone"
                            class="border border-2 border-dashed rounded p-4 text-center bg-light"
                            style="cursor:pointer">
                            <p class="mb-1 fw-bold">Drag & drop Excel files here</p>
                            <p class="text-muted mb-0">or click to select (.xlsx only)</p>

                            <input
                                type="file"
                                name="attachments[]"
                                id="fileInput"
                                accept=".xlsx"
                                multiple
                                hidden>
                        </div>

                        <ul id="fileList" class="list-group mt-3"></ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@vite(["resources/js/import.js"])


@endsection