/* ============================================================
   INTERCEPTOR GLOBAL DE FORMULARIOS AJAX (VERSIÓN JQUERY)
   ============================================================ */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Inicializa todos los formularios que tengan el atributo 'data-ajax'.
        $(document).on('submit', 'form[data-ajax]', handleFormSubmit);
    });

    /**
     * Maneja el evento de envío asíncrono para los formularios interceptados.
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('[type="submit"]');

        // Previene el envío si el formulario está bloqueado.
        if ($submitBtn.length && $submitBtn.prop('disabled')) {
            return;
        }

        toggleLoadingState($submitBtn, true);

        // Extrae todos los datos del formulario (incluyendo archivos si los hubiera)
        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            dataType: 'json'
        })
        .done(function(data) {
            handleServerResponse(data, $form);
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error('❌ Error AJAX:', errorThrown);
            showNotification('Error de conexión con el servidor', 'danger', 'Error');
        })
        .always(function() {
            toggleLoadingState($submitBtn, false);
        });
    }

    /**
     * Procesa la respuesta JSON del servidor y ejecuta las acciones correspondientes.
     */
    function handleServerResponse(data, $form) {
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
                highlightFormErrors($form, data.errores);
            }
        }
    }

    /**
     * Marca visualmente los campos que contienen errores de validación.
     */
    function highlightFormErrors($form, errores) {
        $form.find('.is-invalid').removeClass('is-invalid');

        Object.keys(errores).forEach(name => {
            const $field = $form.find(`[name="${name}"]`);
            if ($field.length) {
                $field.addClass('is-invalid');

                let $feedback = $field.parent().find('.invalid-feedback');
                if (!$feedback.length) {
                    $feedback = $('<div class="invalid-feedback d-block"></div>');
                    $field.parent().append($feedback);
                }
                $feedback.text(errores[name]);
            }
        });
    }

    /**
     * Muestra u oculta el estado de carga en el botón de envío.
     */
    function toggleLoadingState($button, isLoading) {
        if (!$button || !$button.length) return;

        if (isLoading) {
            $button.data('original-html', $button.html());
            $button.prop('disabled', true);
            $button.html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando...');
        } else {
            $button.prop('disabled', false);
            const originalHtml = $button.data('original-html');
            if (originalHtml) {
                $button.html(originalHtml);
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

})(jQuery);