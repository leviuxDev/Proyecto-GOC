/* ============================================================
   INTERCEPTOR GLOBAL DE FORMULARIOS AJAX
   ============================================================ */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa todos los formularios que tengan el atributo 'data-ajax'.
        document.querySelectorAll('form[data-ajax]').forEach(initFormAjax);
    });

    /**
     * Asigna los eventos necesarios a un formulario para su envío asíncrono.
     */
    function initFormAjax(form) {
        form.addEventListener('submit', async function(e) {
            const submitBtn = form.querySelector('[type="submit"]');
            
            // Previene el envío si el formulario está bloqueado.
            if (submitBtn && submitBtn.disabled) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            toggleLoadingState(submitBtn, true);

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                handleServerResponse(data, form);

            } catch (err) {
                console.error('❌ Error AJAX:', err);
                showNotification('Error de conexión con el servidor', 'danger', 'Error');
            } finally {
                toggleLoadingState(submitBtn, false);
            }
        });
    }

    /**
     * Procesa la respuesta JSON del servidor y ejecuta las acciones correspondientes.
     */
    function handleServerResponse(data, form) {
        // Redirección forzada desde el servidor.
        if (data.redirect) {
            window.location.href = data.redirect;
            return;
        }

        if (data.status === 'success') {
            showNotification(data.mensaje, 'success', '¡Éxito!');
            if (data.url_redirect) {
                setTimeout(() => window.location.href = data.url_redirect, 800);
            }
        } else {
            showNotification(data.mensaje || 'Error al procesar el formulario', 'danger', 'Error de Validación');
            if (data.errores && typeof data.errores === 'object') {
                highlightFormErrors(form, data.errores);
            }
        }
    }

    /**
     * Marca visualmente los campos que contienen errores de validación.
     */
    function highlightFormErrors(form, errores) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        Object.keys(errores).forEach(name => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field) {
                field.classList.add('is-invalid');
                
                let feedback = field.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback d-block';
                    field.parentNode.appendChild(feedback);
                }
                feedback.textContent = errores[name];
            }
        });
    }

    /**
     * Muestra u oculta el estado de carga en el botón de envío.
     */
    function toggleLoadingState(button, isLoading) {
        if (!button) return;
        
        if (isLoading) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando...';
        } else {
            button.disabled = false;
            if (button.dataset.originalHtml) {
                button.innerHTML = button.dataset.originalHtml;
            }
        }
    }

    /**
     * Invoca el sistema de notificaciones global si está disponible.
     */
    function showNotification(message, type, title) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(message, type, title);
        }
    }


















})();