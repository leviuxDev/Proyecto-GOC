
(function ($) {
    'use strict';

    function initAll() {
        $('form[data-validate]').each(function () {
            initForm($(this));
        });
    }

    $(document).ready(initAll);

    function initForm($form) {
        const $fields = $form.find(
            'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]):not([type=file]), select, textarea'
        );
        const $submitBtn = $form.find('button[type="submit"]');
        
        if ($submitBtn.length) $submitBtn.prop('disabled', true);

        $fields.each(function () {
            const $field = $(this);
            
            $field.attr('data-touched', 'false');
            if ($field.hasClass('is-invalid')) {
                $field.attr('data-touched', 'true');
            }

            $field.on('blur', function () {
                $field.attr('data-touched', 'true');
                validateField($field);
                checkFormValidity($form, $fields, $submitBtn);
            });

            $field.on('input', function () {
                $field.attr('data-touched', 'true');
                validateField($field);
                actualizarContador($field);
                checkFormValidity($form, $fields, $submitBtn);
            });

            $field.on('change', function () {
                if ($field.attr('type') === 'checkbox' || $field.prop('tagName') === 'SELECT' || $field.attr('type') === 'date') {
                    $field.attr('data-touched', 'true');
                    validateField($field);
                }
                checkFormValidity($form, $fields, $submitBtn);
            });

            actualizarContador($field);
        });

        $form.on('submit', function (e) {
            let allValid = true;
            let $firstError = null;

            $fields.each(function () {
                const $field = $(this);
                $field.attr('data-touched', 'true');
                if (!validateField($field)) {
                    allValid = false;
                    if (!$firstError) $firstError = $field;
                }
            });

            if (!allValid) {
                e.preventDefault();
                e.stopPropagation();
                if ($firstError && $firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 350, function () {
                        $firstError.focus();
                    });
                }
                if (navigator.vibrate) navigator.vibrate([80, 40, 80]);
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('Por favor, corrige los errores en el formulario antes de guardar.', 'danger', 'Error de Validación');
                }
                return false;
            }

            if ($submitBtn.length) {
                $submitBtn.prop('disabled', true);
                const original = $submitBtn.html();
                $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando...');
                setTimeout(() => {
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html(original);
                }, 6000);
            }
        });
    }

    function checkFormValidity($form, $fields, $submitBtn) {
        if (!$submitBtn || !$submitBtn.length) return;
        let isValid = true;

        $fields.each(function () {
            const $field = $(this);
            if ($field.prop('disabled') || $field.prop('readOnly')) return;
            if (validateField($field, true) === false) isValid = false;
        });

        $submitBtn.prop('disabled', !isValid);
    }

    function validateField($field, silent = false) {
        if ($field.prop('disabled') || $field.prop('readOnly')) return true;
        
        // ⭐ CORRECCIÓN AQUÍ: Aseguramos que el valor sea un string plano antes del trim()
        let rawVal = $field.val();
        if (Array.isArray(rawVal)) {
            rawVal = rawVal.join(','); // Si es un select múltiple, lo unimos
        }
        const value = (rawVal != null ? String(rawVal) : '').trim();
        
        let error = '';

        if ($field.attr('required') && value === '') {
            error = $field.data('msgRequired') || 'Este campo es obligatorio';
        }
        else if ($field.attr('type') === 'checkbox' && $field.attr('required') && !$field.prop('checked')) {
            error = $field.data('msgRequired') || 'Debes marcar esta casilla';
        }
        else if (value && $field.attr('minlength') && value.length < parseInt($field.attr('minlength'), 10)) {
            error = $field.data('msgMinlength') || `Debe tener al menos ${$field.attr('minlength')} caracteres`;
        }
        else if (value && $field.attr('maxlength') && value.length > parseInt($field.attr('maxlength'), 10)) {
            error = $field.data('msgMaxlength') || `No puede superar ${$field.attr('maxlength')} caracteres`;
        }
        else if (value && $field.attr('pattern')) {
            try {
                const regex = new RegExp('^(?:' + $field.attr('pattern') + ')$', 'u');
                if (!regex.test(value)) error = $field.data('msgPattern') || 'El formato no es válido';
            } catch (err) { /* pattern inválido, ignorar */ }
        }
        else if ($field.attr('type') === 'email' && value) {
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!regex.test(value)) error = $field.data('msgEmail') || 'Ingresa un correo válido';
        }
        else if ($field.attr('type') === 'tel' && value) {
            const regex = /^\+?[\d\s\-\(\)\.]{7,20}$/;
            if (!regex.test(value)) error = $field.data('msgTel') || 'Ingresa un teléfono válido';
        }
        else if ($field.attr('type') === 'number' && value !== '') {
            const regexNum = /^-?\d+(\.\d+)?$/;
            if (!regexNum.test(value)) {
                error = 'Debe ser un número válido';
            } else {
                const num = parseFloat(value);
                if ($field.attr('min') && num < parseFloat($field.attr('min'))) error = $field.data('msgMin') || `El valor mínimo es ${$field.attr('min')}`;
                else if ($field.attr('max') && num > parseFloat($field.attr('max'))) error = $field.data('msgMax') || `El valor máximo es ${$field.attr('max')}`;
            }
        }
        else if ($field.attr('type') === 'date' && value) {
            const regexFecha = /^\d{4}-\d{2}-\d{2}$/;
            if (!regexFecha.test(value)) {
                error = 'Formato de fecha inválido (use YYYY-MM-DD)';
            } else if ($field.attr('min') && value < $field.attr('min')) {
                error = `La fecha no puede ser anterior a ${$field.attr('min')}`;
            } else if ($field.attr('max') && value > $field.attr('max')) {
                error = `La fecha no puede ser posterior a ${$field.attr('max')}`;
            }
        }

        
        if (!error && value && $field.is('[data-differs-from]')) {
            const otroId = $field.attr('data-differs-from');
            const $otro = $('#' + otroId);
            if ($otro.length) {
                let otroVal = $otro.val();
                if (Array.isArray(otroVal)) otroVal = otroVal.join(',');
                otroVal = (otroVal != null ? String(otroVal) : '').trim();
                if (otroVal && otroVal === value) {
                    error = $field.data('msgDiffers') || 'Este valor no puede ser igual al otro campo';
                }
            }
        }

        
        if (!error && value && $field.is('[data-same-value-as]')) {
            const otroId = $field.attr('data-same-value-as');
            const $otro = $('#' + otroId);
            if ($otro.length) {
                let otroVal = $otro.val();
                if (Array.isArray(otroVal)) otroVal = otroVal.join(',');
                otroVal = (otroVal != null ? String(otroVal) : '').trim();
                if (otroVal !== value) {
                    error = $field.data('msgSameValueAs') || 'Los valores no coinciden';
                }
            }
        }

        if (!silent) setFieldState($field, error);
        return error === '';
    }

    function setFieldState($field, error) {
        const $feedback = getFeedbackElement($field);
        let val = $field.val();
        if (Array.isArray(val)) val = val.join(',');
        const isEmpty = !val || String(val).trim() === '';

        if (error) {
            $field.removeClass('is-valid').addClass('is-invalid');
            $feedback.attr('class', 'invalid-feedback d-block');
            $feedback.text(error);
            $feedback.attr('data-feedback', 'true');
            $field.attr('aria-invalid', 'true');
        } else if (!isEmpty && !$field.is('[data-no-success]')) {
            $field.removeClass('is-invalid').addClass('is-valid');
            $feedback.attr('class', 'valid-feedback d-block');
            $feedback.text($field.data('msgValid') || '¡Correcto!');
            $feedback.attr('data-feedback', 'true');
            $field.attr('aria-invalid', 'false');
        } else {
            $field.removeClass('is-valid is-invalid');
            $feedback.attr('class', 'form-text');
            $feedback.text($field.data('hint') || '');
            $feedback.attr('data-feedback', 'true');
            $field.removeAttr('aria-invalid');
        }
    }

    function getFeedbackElement($field) {
        let $parent = $field.parent();
        if ($parent.hasClass('input-group')) $parent = $parent.parent();
        
        let $feedback = $parent.find('[data-feedback="true"]');
        if (!$feedback.length) {
            const $phpRendered = $parent.find('.invalid-feedback, .valid-feedback');
            if ($phpRendered.length) {
                $phpRendered.attr('data-feedback', 'true');
                $feedback = $phpRendered;
            }
        }
        if (!$feedback.length) {
            $feedback = $('<div></div>').addClass('form-text').attr('data-feedback', 'true');
            $parent.append($feedback);
        }
        return $feedback;
    }

    function actualizarContador($field) {
        if (!$field.attr('maxlength')) return;
        let $contador = $field.parent().find('.char-counter');
        if (!$contador.length) {
            $contador = $('<small></small>').addClass('char-counter text-muted').css({
                'font-size': '0.72rem',
                'float': 'right'
            });
            $field.parent().append($contador);
        }
        let val = $field.val();
        if (Array.isArray(val)) val = val.join(',');
        const length = (val != null ? String(val) : '').length;
        $contador.text(`${length}/${$field.attr('maxlength')}`);
    }

    window.revalidarCampo = function (field) {
        if (!field) return;
        const $field = $(field);
        const $form = $field.closest('form[data-validate]');
        if (!$form.length) return;
        
        const $fields = $form.find(
            'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]):not([type=file]), select, textarea'
        );
        const $submitBtn = $form.find('button[type="submit"]');
        
        $field.attr('data-touched', 'true');
        validateField($field);
        checkFormValidity($form, $fields, $submitBtn);
    };

})(jQuery);