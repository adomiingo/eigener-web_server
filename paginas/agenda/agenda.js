// Esperamos a que todo el HTML esté cargado antes de ejecutar nada
document.addEventListener('DOMContentLoaded', () => {

    /* ====================================================================
       1. EFECTO ACORDEÓN / ZOOM EN LAS FILAS
       ==================================================================== */
    const taskRows = document.querySelectorAll('.task-row');

    taskRows.forEach(row => {
        // Le damos una animación de entrada en cascada al cargar la página (Efecto visual bonito)
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
        row.style.transition = 'opacity 0.4s ease, transform 0.4s ease, box-shadow 0.3s ease, background-color 0.3s ease';
        
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'scale(1) translateY(0)';
        }, 50 * Array.from(taskRows).indexOf(row)); // Retardo escalonado por fila

        // Evento de clic para hacer zoom y mostrar detalles
        row.addEventListener('click', function(e) {
            // Evitamos que el zoom se active si hacemos clic en un botón o en la última columna (acciones)
            if (e.target.closest('.btn-action') || e.target.closest('td:last-child')) {
                return;
            }

            // Opcional: Cerrar las demás filas antes de abrir esta (Efecto Acordeón)
            taskRows.forEach(r => {
                if (r !== this) r.classList.remove('expanded');
            });

            // Alternar la clase en la fila clickeada
            this.classList.toggle('expanded');
        });
    });

    /* ====================================================================
       2. BÚSQUEDA RÁPIDA EN TIEMPO REAL (Filtro del lado del cliente)
       ==================================================================== */
    // Busca un input de texto que tenga el id "buscadorJS" (te explico abajo cómo añadirlo al HTML)
    const buscador = document.getElementById('buscadorJS');
    
    if (buscador) {
        buscador.addEventListener('keyup', function() {
            const textoBusqueda = this.value.toLowerCase();

            taskRows.forEach(row => {
                // Buscamos el texto dentro del título (strong) de cada fila
                const titulo = row.querySelector('strong').textContent.toLowerCase();
                
                // Si el título incluye lo que escribimos, mostramos la fila, si no, la ocultamos
                if (titulo.includes(textoBusqueda)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    /* ====================================================================
       3. AUTO-OCULTAR ALERTAS DE PHP
       ==================================================================== */
    // Si guardas una tarea y sale el mensaje de "Éxito", esto lo oculta suavemente tras 4 segundos
    const alertas = document.querySelectorAll('.alert');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.opacity = '0';
            alerta.style.transition = 'opacity 0.5s ease';
            // Esperamos a que termine la transición de opacidad para eliminar el elemento del DOM
            setTimeout(() => alerta.remove(), 500);
        }, 4000);
    });

    /* ====================================================================
       4. CONFIRMACIÓN DE BORRADO MÁS SEGURA
       ==================================================================== */
    // En lugar de poner onclick="..." en el HTML, lo manejamos desde aquí
    const botonesBorrar = document.querySelectorAll('.btn-del');
    botonesBorrar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            const confirmado = confirm('¿Estás seguro de que deseas borrar esta tarea definitivamente?');
            if (!confirmado) {
                e.preventDefault(); // Si el usuario cancela, detenemos el enlace
            }
        });
    });

});