/* =========================================================
   SISTEMA DE VALIDACIÓN EN TIEMPO REAL v8
   - Regla: data-differs-from (campos que no pueden ser iguales)
   - Regla: data-same-value-as (campos que deben ser iguales, ej. confirmar contraseña)
   - Expuesta función global window.revalidarCampo
   ========================================================= */
(function () {
    'use strict';

    function initAll() {
        document.querySelectorAll('form[data-validate]').forEach(initForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    function initForm(form) {
        const fields = form.querySelectorAll(
            'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]):not([type=file]), select, textarea'
        );
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        fields.forEach(field => {
            field.dataset.touched = 'false';
            if (field.classList.contains('is-invalid')) field.dataset.touched = 'true';

            field.addEventListener('blur', () => {
                field.dataset.touched = 'true';
                validateField(field);
                checkFormValidity(form, fields, submitBtn);
            });

            field.addEventListener('input', () => {
                if (field.dataset.touched === 'true') validateField(field);
                actualizarContador(field);
                checkFormValidity(form, fields, submitBtn);
            });

            field.addEventListener('change', () => {
                if (field.type === 'checkbox' || field.tagName === 'SELECT' || field.type === 'date') {
                    field.dataset.touched = 'true';
                    validateField(field);
                }
                checkFormValidity(form, fields, submitBtn);
            });

            actualizarContador(field);
        });

        form.addEventListener('submit', function (e) {
            let allValid = true;
            let firstError = null;
            fields.forEach(field => {
                field.dataset.touched = 'true';
                if (!validateField(field)) {
                    allValid = false;
                    if (!firstError) firstError = field;
                }
            });
            if (!allValid) {
                e.preventDefault();
                e.stopPropagation();
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => firstError.focus(), 350);
                }
                if (navigator.vibrate) navigator.vibrate([80, 40, 80]);
                if (typeof mostrarNotificacion === 'function') {
                    mostrarNotificacion('Por favor, corrige los errores en el formulario antes de guardar.', 'danger', 'Error de Validación');
                }
                return false;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                const original = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
                setTimeout(() => { submitBtn.disabled = false; submitBtn.innerHTML = original; }, 6000);
            }
        });
    }

    function checkFormValidity(form, fields, submitBtn) {
        if (!submitBtn) return;
        let isValid = true;
        fields.forEach(field => {
            if (field.disabled || field.readOnly) return;
            if (validateField(field, true) === false) isValid = false;
        });
        submitBtn.disabled = !isValid;
    }

    function validateField(field, silent = false) {
        if (field.disabled || field.readOnly) return true;
        const value = (field.value || '').trim();
        let error = '';

        if (field.hasAttribute('required') && value === '') {
            error = field.dataset.msgRequired || 'Este campo es obligatorio';
        }
        else if (field.type === 'checkbox' && field.hasAttribute('required') && !field.checked) {
            error = field.dataset.msgRequired || 'Debes marcar esta casilla';
        }
        else if (value && field.hasAttribute('minlength') && value.length < parseInt(field.getAttribute('minlength'))) {
            error = field.dataset.msgMinlength || `Debe tener al menos ${field.getAttribute('minlength')} caracteres`;
        }
        else if (value && field.hasAttribute('maxlength') && value.length > parseInt(field.getAttribute('maxlength'))) {
            error = field.dataset.msgMaxlength || `No puede superar ${field.getAttribute('maxlength')} caracteres`;
        }
        else if (value && field.hasAttribute('pattern')) {
            try {
                const regex = new RegExp('^(?:' + field.getAttribute('pattern') + ')$', 'u');
                if (!regex.test(value)) error = field.dataset.msgPattern || 'El formato no es válido';
            } catch (err) { /* pattern inválido, ignorar */ }
        }
        else if (field.type === 'email' && value) {
            const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!regex.test(value)) error = field.dataset.msgEmail || 'Ingresa un correo válido';
        }
        else if (field.type === 'tel' && value) {
            const regex = /^\+?[\d\s\-\(\)\.]{7,20}$/;
            if (!regex.test(value)) error = field.dataset.msgTel || 'Ingresa un teléfono válido';
        }
        else if (field.type === 'number' && value !== '') {
            const regexNum = /^-?\d+(\.\d+)?$/;
            if (!regexNum.test(value)) {
                error = 'Debe ser un número válido';
            } else {
                const num = parseFloat(value);
                if (field.hasAttribute('min') && num < parseFloat(field.getAttribute('min'))) error = field.dataset.msgMin || `El valor mínimo es ${field.getAttribute('min')}`;
                else if (field.hasAttribute('max') && num > parseFloat(field.getAttribute('max'))) error = field.dataset.msgMax || `El valor máximo es ${field.getAttribute('max')}`;
            }
        }
        else if (field.type === 'date' && value) {
            const regexFecha = /^\d{4}-\d{2}-\d{2}$/;
            if (!regexFecha.test(value)) {
                error = 'Formato de fecha inválido (use YYYY-MM-DD)';
            } else if (field.hasAttribute('min') && value < field.getAttribute('min')) {
                error = `La fecha no puede ser anterior a ${field.getAttribute('min')}`;
            } else if (field.hasAttribute('max') && value > field.getAttribute('max')) {
                error = `La fecha no puede ser posterior a ${field.getAttribute('max')}`;
            }
        }

        // ⭐ REGLA: data-differs-from (no puede ser igual al otro campo)
        
        if (!error && value && field.hasAttribute('data-differs-from')) {
            const otroId = field.getAttribute('data-differs-from');
            const otro = document.getElementById(otroId);
            if (otro && otro.value && otro.value === value) {
                error = field.dataset.msgDiffers || 'Este valor no puede ser igual al otro campo';
            }
        }

        // ⭐ NUEVA REGLA: data-same-value-as (debe ser igual al otro campo)
        if (!error && value && field.hasAttribute('data-same-value-as')) {
            const otroId = field.getAttribute('data-same-value-as');
            const otro = document.getElementById(otroId);
            if (otro && otro.value !== value) {
                error = field.dataset.msgSameValueAs || 'Los valores no coinciden';
            }
        }

        if (!silent) setFieldState(field, error);
        return error === '';
    }

    function setFieldState(field, error) {
        const feedback = getFeedbackElement(field);
        const isEmpty = !field.value || field.value.trim() === '';

        if (error) {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            feedback.className = 'invalid-feedback d-block';
            feedback.textContent = error;
            feedback.setAttribute('data-feedback', 'true');
            field.setAttribute('aria-invalid', 'true');
        } else if (!isEmpty && !field.hasAttribute('data-no-success')) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            feedback.className = 'valid-feedback d-block';
            feedback.textContent = field.dataset.msgValid || '¡Correcto!';
            feedback.setAttribute('data-feedback', 'true');
            field.setAttribute('aria-invalid', 'false');
        } else {
            field.classList.remove('is-valid', 'is-invalid');
            feedback.className = 'form-text';
            feedback.textContent = field.dataset.hint || '';
            feedback.setAttribute('data-feedback', 'true');
            field.removeAttribute('aria-invalid');
        }
    }

    function getFeedbackElement(field) {
        let parent = field.parentNode;
        if (parent.classList.contains('input-group')) parent = parent.parentNode;
        let feedback = parent.querySelector('[data-feedback="true"]');
        if (!feedback) {
            const phpRendered = parent.querySelector('.invalid-feedback, .valid-feedback');
            if (phpRendered) {
                phpRendered.setAttribute('data-feedback', 'true');
                feedback = phpRendered;
            }
        }
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'form-text';
            feedback.setAttribute('data-feedback', 'true');
            parent.appendChild(feedback);
        }
        return feedback;
    }

    function actualizarContador(field) {
        if (!field.hasAttribute('maxlength')) return;
        let contador = field.parentNode.querySelector('.char-counter');
        if (!contador) {
            contador = document.createElement('small');
            contador.className = 'char-counter text-muted';
            contador.style.cssText = 'font-size:0.72rem;float:right;';
            field.parentNode.appendChild(contador);
        }
        contador.textContent = `${(field.value || '').length}/${field.getAttribute('maxlength')}`;
    }

    // ⭐ FUNCIÓN GLOBAL PARA REVALIDAR UN CAMPO DESDE CUALQUIER SCRIPT
    window.revalidarCampo = function(field) {
        if (!field) return;
        const form = field.closest('form[data-validate]');
        if (!form) return;
        const fields = form.querySelectorAll(
            'input:not([type=hidden]):not([type=submit]):not([type=button]):not([type=reset]):not([type=file]), select, textarea'
        );
        const submitBtn = form.querySelector('button[type="submit"]');
        field.dataset.touched = 'true';
        validateField(field);
        checkFormValidity(form, fields, submitBtn);
    };
})();