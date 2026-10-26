// Rellenar correo de demostración en login.php
function seleccionarCuentaDemo(correo) {
    const inputEmail = document.getElementById('emailInput');
    if (inputEmail) {
        inputEmail.value = correo;
        inputEmail.focus();
    }
}

// Cargar caso de ejemplo del documento guía en nuevo_ticket.php
function cargarEjemploGuia() {
    const srv  = document.getElementById('selectServicio');
    const cat  = document.getElementById('selectCategoria');
    const prio = document.getElementById('selectPrioridad');
    const asu  = document.getElementById('inputAsunto');
    const desc = document.getElementById('inputDescripcion');

    if (srv)  srv.value  = '1';
    if (cat)  cat.value  = '1';
    if (prio) prio.value = '3';
    if (asu)  asu.value  = 'No puedo acceder al sistema académico';
    if (desc) {
        desc.value = 'No puedo acceder al sistema académico. Al ingresar mi usuario y contraseña aparece un mensaje indicando que las credenciales son incorrectas.';
    }
}

// Asignar decisión del técnico (Aceptada, Modificada, Descartada) en detalle_ticket.php
function fijarDecisionRag(boton, valorDecision) {
    const form = boton.closest('form');
    if (form) {
        const inputDecision = form.querySelector('input[name="decision_tecnico"]');
        if (inputDecision) {
            inputDecision.value = valorDecision;
        }
    }
}