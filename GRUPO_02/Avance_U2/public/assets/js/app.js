document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            if (emailInput) {
                emailInput.value = button.dataset.demoEmail || '';
                emailInput.focus();
            }

            if (passwordInput && button.dataset.demoPassword) {
                passwordInput.value = button.dataset.demoPassword;
            }
        });
    });

    const togglePassword = document.querySelector('[data-toggle-password]');

    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const passwordInput = document.getElementById('password');
            const icon = togglePassword.querySelector('i');

            if (!passwordInput) {
                return;
            }

            const mostrar = passwordInput.type === 'password';
            passwordInput.type = mostrar ? 'text' : 'password';

            if (icon) {
                icon.className = mostrar ? 'bi bi-eye-slash' : 'bi bi-eye';
            }
        });
    }

    const servicioSelect = document.querySelector('[data-service-select]');
    const categoriaSelect = document.querySelector('[data-category-select]');

    if (servicioSelect && categoriaSelect) {
        const filtrarCategorias = () => {
            const servicioId = servicioSelect.value;
            let seleccionValida = false;

            categoriaSelect.querySelectorAll('option[data-service]').forEach((option) => {
                const coincide = servicioId !== '' && option.dataset.service === servicioId;
                option.hidden = !coincide;
                option.disabled = !coincide;

                if (option.selected && coincide) {
                    seleccionValida = true;
                }
            });

            if (!seleccionValida) {
                categoriaSelect.value = '';
            }
        };

        servicioSelect.addEventListener('change', filtrarCategorias);
        filtrarCategorias();
    }

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    const decisionesRag = document.querySelectorAll('[data-rag-decision]');

    if (decisionesRag.length > 0) {
        const solucionFinal = document.getElementById('solucion_final_tecnico');
        const comentario = document.getElementById('comentario_tecnico');
        const envolturaSolucion = document.querySelector('[data-rag-final-wrapper]');

        const actualizarDecision = () => {
            const seleccionada = document.querySelector('[data-rag-decision]:checked');
            const decision = seleccionada ? seleccionada.value : 'Aceptada';

            if (solucionFinal) {
                solucionFinal.required = decision === 'Modificada';
            }

            if (comentario) {
                comentario.required = decision === 'Descartada';
            }

            if (envolturaSolucion) {
                envolturaSolucion.classList.toggle('d-none', decision === 'Descartada');
            }
        };

        decisionesRag.forEach((radio) => {
            radio.addEventListener('change', actualizarDecision);
        });

        actualizarDecision();
    }

    document.querySelectorAll('[data-rag-copy]').forEach((button) => {
        button.addEventListener('click', () => {
            const destino = document.getElementById('solucion_aplicada');

            if (!destino) {
                return;
            }

            destino.value = button.dataset.ragCopy || '';
            destino.dispatchEvent(new Event('input', { bubbles: true }));
            destino.scrollIntoView({ behavior: 'smooth', block: 'center' });
            destino.focus();
        });
    });
});
