$(function () {
    if (!window.FormHelper) {
        return;
    }

    FormHelper.attachAjaxForm({
        formSelector: '#passwordLoginForm',
        buttonSelector: '#passwordSubmitBtn',
        alertSelector: '#loginAlert',
        defaultText: 'Login with Password',
        loadingText: 'Signing in...',
        rules: {
            email: { required: true, email: true },
            password: { required: true, minlength: 8 }
        },
        messages: {
            email: {
                required: 'Please enter your email address.',
                email: 'Please enter a valid email address.'
            },
            password: {
                required: 'Please enter your password.',
                minlength: 'Password must be at least 8 characters long.'
            }
        },
        fallbackErrorMessage: 'Unable to sign in right now. Please try again.',
        onSuccess: function (response) {
            FormHelper.showAlert($('#loginAlert'), 'success', 'Login successful. Redirecting...');
            window.location.href = response.redirect || '/home';
        }
    });

    FormHelper.attachAjaxForm({
        formSelector: '#otpSendForm',
        buttonSelector: '#otpSendBtn',
        alertSelector: '#loginAlert',
        defaultText: 'Send OTP to Email',
        loadingText: 'Sending OTP...',
        rules: {
            email: { required: true, email: true }
        },
        messages: {
            email: {
                required: 'Please enter your email address.',
                email: 'Please enter a valid email address.'
            }
        },
        fallbackErrorMessage: 'Unable to send OTP right now. Please try again.',
        onSuccess: function (response) {
            FormHelper.showAlert($('#loginAlert'), 'success', response.message || 'OTP sent successfully. Redirecting...');
            window.location.href = response.redirect || '/login/otp';
        }
    });
});
