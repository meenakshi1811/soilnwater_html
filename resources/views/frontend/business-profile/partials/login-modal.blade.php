{{-- Shown to guests in place of the enquiry and service popups. $loginName --}}
<div class="modal fade consultant-login-required-modal" id="bizLoginRequiredModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title">Log in to continue</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="consultant-login-required-card">
                    <div class="consultant-login-required-card__icon"><i class="fa-solid fa-lock"></i></div>
                    <div>
                        <h4>You are not logged in</h4>
                        <p>Please log in to see the details and send an enquiry to {{ $loginName }}.</p>
                        <a href="{{ route('login') }}" class="consultant-login-required-card__btn">Login to continue</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
