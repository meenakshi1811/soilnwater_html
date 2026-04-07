$(function () {
    if (!window.FormHelper) {
        return;
    }

    FormHelper.attachAjaxForm({
        formSelector: '#otpVerifyForm',
        buttonSelector: '#otpVerifyBtn',
        alertSelector: '#otpAlert',
        defaultText: 'Verify & Login',
        loadingText: 'Verifying...',
        rules: {
            otp: { required: true, digits: true, minlength: 6, maxlength: 6 }
        },
        messages: {
            otp: {
                required: 'Please enter the OTP sent to your email.',
                digits: 'OTP must contain only numbers.',
                minlength: 'OTP must be 6 digits.',
                maxlength: 'OTP must be 6 digits.'
            }
        },
        fallbackErrorMessage: 'Unable to verify OTP right now. Please try again.',
        onSuccess: function (response) {
            FormHelper.showAlert($('#otpAlert'), 'success', 'OTP verified. Redirecting...');
            window.location.href = response.redirect || '/home';
        }
    });
});
