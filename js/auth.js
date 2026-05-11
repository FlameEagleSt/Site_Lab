let currentUser = null;
let profileDropdownOpen = false;

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateField($input, errorMsg) {
    const $formGroup = $input.closest('.form-group');
    const $errorSpan = $formGroup.find('.error-message');

    if (errorMsg) {
        $input.addClass('error').removeClass('success');
        $errorSpan.text(errorMsg).show();
        return false;
    } else {
        $input.removeClass('error').addClass('success');
        $errorSpan.hide();
        return true;
    }
}

function clearValidation($input) {
    $input.removeClass('error success');
    $input.closest('.form-group').find('.error-message').hide();
}

$(document).ready(function() {

    $(document).on('click', '#profile-btn', function(e) {
        e.stopPropagation();
        profileDropdownOpen = !profileDropdownOpen;
        $('#profile-dropdown').toggleClass('show');
    });

    $(document).on('click', function(e) {
        if (profileDropdownOpen && !$(e.target).closest('#profile-dropdown').length && !$(e.target).closest('#profile-btn').length) {
            $('#profile-dropdown').removeClass('show');
            profileDropdownOpen = false;
        }
    });

    $(document).on('click', '#login-link', function(e) {
        e.preventDefault();
        $('#profile-dropdown').removeClass('show');
        profileDropdownOpen = false;
        openModal('login-modal');
    });

    $(document).on('click', '#register-link', function(e) {
        e.preventDefault();
        $('#profile-dropdown').removeClass('show');
        profileDropdownOpen = false;
        openModal('register-modal');
    });

    $(document).on('click', '#add-product-link', function(e) {
        e.preventDefault();
        $('#profile-dropdown').removeClass('show');
        profileDropdownOpen = false;
        openModal('add-product-modal');
    });

    $(document).on('click', '#logout-link', function(e) {
        $('#profile-dropdown').removeClass('show');
        profileDropdownOpen = false;
        renderCart();
        alert('Вы вышли из профиля');
    });

    $('#login-form').on('submit', function(e) {
        let isValid = true;

        const email = $('#login-email').val().trim();
        const password = $('#login-password').val();

        if (!email) {
            validateField($('#login-email'), 'Введите email');
            isValid = false;
        } else if (!validateEmail(email)) {
            validateField($('#login-email'), 'Неверный формат email (должны быть @ и .)');
            isValid = false;
        } else {
            validateField($('#login-email'), '');
        }

        if (!password) {
            validateField($('#login-password'), 'Введите пароль');
            isValid = false;
        } else if (password.length < 6) {
            validateField($('#login-password'), 'Пароль должен содержать минимум 6 символов');
            isValid = false;
        } else {
            validateField($('#login-password'), '');
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    $('#register-form').on('submit', function(e) {
        let isValid = true;
        const surname = $('#register-surname').val().trim();
        const name = $('#register-name').val().trim();
        const patronymic = $('#register-patronymic').val().trim();
        const email = $('#register-email').val().trim();
        const password = $('#register-password').val();

        if (!surname) {
            validateField($('#register-surname'), 'Введите фамилию');
            isValid = false;
        } else if (surname.length < 2) {
            validateField($('#register-surname'), 'Фамилия должна содержать минимум 2 символа');
            isValid = false;
        } else {
            validateField($('#register-surname'), '');
        }

        if (!name) {
            validateField($('#register-name'), 'Введите имя');
            isValid = false;
        } else if (name.length < 2) {
            validateField($('#register-name'), 'Имя должно содержать минимум 2 символа');
            isValid = false;
        } else {
            validateField($('#register-name'), '');
        }

        if (!email) {
            validateField($('#register-email'), 'Введите email');
            isValid = false;
        } else if (!validateEmail(email)) {
            validateField($('#register-email'), 'Неверный формат email (должны быть @ и .)');
            isValid = false;
        } else {
            validateField($('#register-email'), '');
        }

        if (!password) {
            validateField($('#register-password'), 'Введите пароль');
            isValid = false;
        } else if (password.length < 6) {
            validateField($('#register-password'), 'Пароль должен содержать минимум 6 символов');
            isValid = false;
        } else {
            validateField($('#register-password'), '');
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    $('#login-email, #register-email').on('blur', function() {
        const email = $(this).val().trim();
        if (email && !validateEmail(email)) {
            validateField($(this), 'Неверный формат email (должны быть @ и .)');
        } else if (email) {
            validateField($(this), '');
        }
    });

    $('#login-password, #register-password').on('input', function() {
        const password = $(this).val();
        if ($(this).hasClass('error') && password.length >= 6) {
            validateField($(this), '');
        }
    });

    $('#register-surname, #register-name').on('input', function() {
        const value = $(this).val().trim();
        if ($(this).hasClass('error') && value.length >= 2) {
            validateField($(this), '');
        }
    });

    $('.form-input').on('input', function() {
        if ($(this).hasClass('error')) {
            clearValidation($(this));
        }
    });
});
