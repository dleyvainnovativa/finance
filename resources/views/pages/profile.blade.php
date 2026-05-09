@extends('main')

@section('content')
<input type="hidden" value="{{route('api.profile')}}" id="data_url">
<div class="d-flex justify-content-between align-items-center mb-2">
    <div>
        <h3 id="main_title" class="display">Perfil</h3>
        <p class="text-muted pb-0 mb-0">Manage your journal entries</p>
    </div>

</div>
<div class="row g-4 mt-1">
    <div class="col-12 col-lg-8 col-xl-8">
        <div class="card card-dark border border-dark shadow-sm">
            <div class="card-body p-4 text-dark">
                <form class="needs-validation" id="change_profile_form" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">
                                Cambiar datos de perfil
                            </h6>

                            <small class="text-muted">
                                Cambia tu nombre. Deja el campo vacío si no deseas cambiarlo
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Nombre
                            </label>
                            <input
                                type="text"
                                name="name" id="profile_name"
                                class="form-control card-dark border border-dark text-dark"
                                value="{{ session('user_name') }}"
                                required>
                        </div>
                        {{-- EMAIL --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Correo electrónico
                            </label>
                            <input
                                type="email" id="profile_email"
                                class="form-control card-dark border border-dark text-dark bg-light"
                                value="{{ session('user_email') }}"
                                disabled>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100">
                                Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8 col-xl-4">
        <div class="card card-dark border border-dark shadow-sm">
            <div class="card-body p-4 text-dark">
                <form class="needs-validation" id="change_password_form" novalidate>
                    <div class="row g-3">
                        {{-- PASSWORD --}}
                        <div class="col-12">
                            <h6 class="fw-bold mb-0">
                                Cambiar contraseña
                            </h6>

                            <small class="text-muted">
                                Déjalo vacío si no deseas cambiarla
                            </small>
                        </div>
                        {{-- CURRENT PASSWORD --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Contraseña actual
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="password"
                                    id="current_password"
                                    autocomplete="current-password"
                                    class="form-control card-dark border border-dark text-dark" required>

                                <span
                                    class="input-group-text text-bg-dark border border-dark toggle-password"
                                    data-target="current_password"
                                    style="cursor: pointer;">

                                    <i class="fa-regular fa-eye"></i>

                                </span>

                            </div>
                        </div>

                        {{-- NEW PASSWORD --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Nueva contraseña
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="new_password"
                                    id="new_password"
                                    autocomplete="new-password"
                                    class="form-control card-dark border border-dark text-dark" required>

                                <span
                                    class="input-group-text text-bg-dark border border-dark toggle-password"
                                    data-target="new_password"
                                    style="cursor: pointer;">

                                    <i class="fa-regular fa-eye"></i>

                                </span>

                            </div>
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Confirmar contraseña
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="new_password_confirmation"
                                    id="new_password_confirmation"
                                    autocomplete="new-password"
                                    class="form-control card-dark border border-dark text-dark" required>

                                <span
                                    class="input-group-text text-bg-dark border border-dark toggle-password"
                                    data-target="new_password_confirmation"
                                    style="cursor: pointer;">

                                    <i class="fa-regular fa-eye"></i>

                                </span>

                            </div>
                        </div>
                        {{-- BUTTON --}}
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary w-100">
                                Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/profile.js'])
@endsection