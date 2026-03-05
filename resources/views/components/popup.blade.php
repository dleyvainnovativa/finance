<div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-dark border border-dark">
            <div class="modal-body p-4 text-center text-dark">
                <form class="needs-validation" id="confirmation_popup">
                    <div class="row g-3 p-3">
                        <div class="col-12">
                            <div id="warning_popup_icon" class="d-none feature-icon bg-danger-subtle d-inline-flex align-items-center justify-content-center">
                                <i class="fas fa-circle-exclamation fa-2xl text-danger"></i>
                            </div>
                            <div id="confirm_popup_icon" class="feature-icon bg-success-subtle d-inline-flex align-items-center justify-content-center">
                                <i class="fas fa-check-circle fa-2xl text-success"></i>
                            </div>
                        </div>
                        <div class="col-12">
                            <h3 class="fw-bold text-dark" id="popup_title">¿Estás seguro?</h3>
                        </div>
                        <div class="col-12">
                            <p class="text-muted" id="popup_text">This action cannot be undone. All values associated with this field will be lost</p>
                        </div>
                        <div class="col-12">
                            <button type="button" id="confirm_popup_btn" class="btn btn-success w-100 ">Confirmar</button>
                            <button type="button" id="warning_popup_btn" class="d-none btn btn-danger w-100 ">Salirme</button>
                        </div>
                        <div class="col-12">
                            <a class="btn btn-light border w-100 " id="cancel_btn" data-bs-dismiss="modal">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>