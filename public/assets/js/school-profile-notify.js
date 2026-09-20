(function () {
    function configureToastr() {
        if (!window.toastr || window.__schoolProfileToastrConfigured) {
            return;
        }

        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
            extendedTimeOut: 2000,
        };
        window.__schoolProfileToastrConfigured = true;
    }

    window.schoolProfileNotify = function (type, message) {
        if (!message) {
            return;
        }

        var toastType = type === 'danger' ? 'error' : type;
        if (!['success', 'error', 'info', 'warning'].includes(toastType)) {
            toastType = 'info';
        }

        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            configureToastr();
            window.toastr[toastType](message);
            return;
        }

        if (window.console && console.warn) {
            console.warn('[school-profile]', toastType, message);
        }
    };
})();
