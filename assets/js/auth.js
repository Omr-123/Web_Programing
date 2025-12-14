$(document).ready(function () {

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function showError(input, message) {
        $(input).addClass('error-border');
        if ($(input).next('.error-message').length === 0) {
            $(input).after(`<span class="error-message">${message}</span>`);
        }
    }

    function clearErrors() {
        $('.error-message').remove();
        $('input').removeClass('error-border');
    }

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;
        const emailInput = $(this).find('input[name="email"]');
        const passInput = $(this).find('input[name="password"]');

        const email = emailInput.val().trim();
        const password = passInput.val().trim();

        if (email === '') {
            showError(emailInput, 'Email is required.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError(emailInput, 'Please enter a valid email.');
            isValid = false;
        }

        if (password === '') {
            showError(passInput, 'Password is required.');
            isValid = false;
        }

        if (isValid) {
            this.submit();
        }
    });

    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;

        // register form uses first_name and last_name fields
        const firstInput = $(this).find('input[name="first_name"]');
        const lastInput = $(this).find('input[name="last_name"]');
        const emailInput = $(this).find('input[name="email"]');
        const passInput = $(this).find('input[name="password"]');
        const confirmPassInput = $(this).find('input[name="confirm_password"]');

        // Ensure all expected inputs exist
        if (!firstInput.length || !lastInput.length || !emailInput.length || !passInput.length || !confirmPassInput.length) {
            // developer-visible warning and a user alert to avoid silent failures
            console.error('Register form is missing one or more required fields.');
            alert('Registration form is incomplete. Please refresh the page and try again.');
            return;
        }

        const first = firstInput.val().trim();
        const last = lastInput.val().trim();
        const email = emailInput.val().trim();
        const password = passInput.val().trim();
        const confirmPass = confirmPassInput.val().trim();

        if (first === '') {
            showError(firstInput, 'First name is required.');
            isValid = false;
        }

        if (last === '') {
            showError(lastInput, 'Last name is required.');
            isValid = false;
        }

        if (email === '') {
            showError(emailInput, 'Email is required.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError(emailInput, 'Please enter a valid email.');
            isValid = false;
        }

        if (password === '') {
            showError(passInput, 'Password is required.');
            isValid = false;
        } else if (password.length < 6) {
            showError(passInput, 'Password must be at least 6 characters.');
            isValid = false;
        }

        if (confirmPass === '') {
            showError(confirmPassInput, 'Please confirm your password.');
            isValid = false;
        } else if (confirmPass !== password) {
            showError(confirmPassInput, 'Passwords do not match.');
            isValid = false;
        }

        if (isValid) {
            // submit the form to server
            this.submit();
        }
    });

    $('input').on('input', function() {
        $(this).removeClass('error-border');
        $(this).next('.error-message').remove();
    });

});