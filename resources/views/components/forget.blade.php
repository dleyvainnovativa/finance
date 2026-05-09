<div class="modal fade" id="forgetModal" tabindex="-1" aria-labelledby="forgetModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content card-dark border border-dark">
            <div class="modal-body p-4 text-center text-dark">
                <form class="needs-validation" id="forget-form">
                    <div class="row g-3 p-3">
                        <div class="col-12">
                            <div id="" class="feature-icon bg-success-subtle d-inline-flex align-items-center justify-content-center">
                                <i class="fas fa-check-circle fa-2xl text-success"></i>
                            </div>
                        </div>
                        <div class="col-12">
                            <h3 class="fw-bold text-dark">¿Olvidaste tu contraseña?</h3>
                        </div>
                        <div class="col-12">
                            <p class="text-muted">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                        </div>
                        <div class="col-12 text-start">
                            <label for="email" class="form-label fw-semibold small text-dark">
                                Correo electrónico
                            </label>
                            <input
                                type="email"
                                class="text-dark form-control card-dark border border-dark"
                                id="forget_email"
                                name="email"
                                placeholder="correo@ejemplo.com"
                                required>
                        </div>
                        <div class="col-12">
                            <button type="submit" id="" class="btn btn-success w-100 ">Enviar Link</button>
                        </div>
                        <div class="col-12">
                            <a class="btn btn-light border w-100" data-bs-dismiss="modal">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>